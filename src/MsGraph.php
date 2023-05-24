<?php

namespace Dcblogdev\MsGraph;

/*
 * msgraph api documentation can be found at https://developer.msgraph.com/reference
 **/

use Dcblogdev\MsGraph\Events\NewMicrosoft365SignInEvent;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\Resources\Contacts;
use Dcblogdev\MsGraph\Resources\Emails;
use Dcblogdev\MsGraph\Resources\Files;
use Dcblogdev\MsGraph\Resources\Tasks;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class MsGraph
{
    public function contacts()
    {
        return new Contacts();
    }

    public function emails()
    {
        return new Emails();
    }

    public function files()
    {
        return new Files();
    }

    public function tasks()
    {
        return new Tasks();
    }

    /**
     * Set the base url that all API requests use.
     * @var string
     */
    protected static $baseUrl = 'https://graph.microsoft.com/v1.0/';

    /**
     * Make a connection or return a token where it's valid.
     * @return mixed
     */
    public function connect($id = null)
    {
        $id = $this->getUserId($id);

        $provider = $this->getProvider();

        if (!$this->isConnected($id)) {
            $token = $this->getTokenData($id);

            if ($token !== null) {
                if ($token->expires < time()) {
                    $user = config('auth.providers.users.model')::find($id);
                    $this->renewExpiringToken($token, $id, $user->email);
                }
            }
        }

        if (!request()->has('code') && !$this->isConnected($id)) {
            return redirect($provider->getAuthorizationUrl());
        } elseif (request()->has('code')) {
            $accessToken = $provider->getAccessToken('authorization_code', ['code' => request('code')]);

            $response = Http::withToken($accessToken->getToken())->get(self::$baseUrl.'me');

            if (auth()->check()) {
                $this->storeToken(
                    $accessToken->getToken(),
                    $accessToken->getRefreshToken(),
                    $accessToken->getExpires(),
                    $id,
                    auth()->user()->email
                );
            } else {
                event(new NewMicrosoft365SignInEvent([
                    'info'         => $response->json(),
                    'accessToken'  => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires'      => $accessToken->getExpires(),
                ]));
            }
        }

        return redirect(config('msgraph.msgraphLandingUri'));
    }

    /**
     * @param $id
     * @return bool
     */
    public function isConnected($id = null)
    {
        $token = $this->getTokenData($id);

        if ($token === null) {
            return false;
        }

        if ($token->expires < time()) {
            return false;
        }

        return true;
    }

    /**
     * logout of application and Microsoft 365, redirects back to the provided path.
     * @param  string  $redirectPath
     * @return RedirectResponse
     */
    public function disconnect($redirectPath = '/', $logout = true)
    {
        if ($logout === true && auth()->check()) {
            auth()->logout();
        }

        return redirect()->away('https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri='.url($redirectPath));
    }

    /**
     * Return authenticated access token or request new token when expired.
     * @param  $id integer - id of the user
     * @param  $returnNullNoAccessToken null when set to true return null
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|\Illuminate\Routing\Redirector string access token
     */
    public function getAccessToken($id = null, $redirectWhenNotConnected = true)
    {
        $token = $this->getTokenData($id);
        $id    = $this->getUserId($id);

        if ($redirectWhenNotConnected) {
            if (!$this->isConnected()) {
                return redirect()->away(config('msgraph.redirectUri'));
            }
        }

        if ($token === null) {
            return null;
        }

        if ($token->expires < time() + 300) {
            $user = config('auth.providers.users.model')::find($id);
            return $this->renewExpiringToken($token, $id, $user->email);
        }

        return $token->access_token;
    }

    /**
     * @param  $id  - integar id of user
     * @return object
     */
    public function getTokenData($id = null)
    {
        $id = $this->getUserId($id);
        return MsGraphToken::where('user_id', $id)->first();
    }

    /**
     * Store token.
     * @param  $access_token string
     * @param  $refresh_token string
     * @param  $expires string
     * @param  $id integer
     * @return object
     */
    public function storeToken($access_token, $refresh_token, $expires, $id, $email)
    {
        return MsGraphToken::updateOrCreate(['user_id' => $id], [
            'user_id'       => $id,
            'email'         => $email,
            'access_token'  => $access_token,
            'expires'       => $expires,
            'refresh_token' => $refresh_token,
        ]);
    }

    /**
     * return array containing previous and next page counts.
     * @param  $data array
     * @param  $total array
     * @param  $limit  integer
     * @param  $skip integer
     * @return array
     */
    public function getPagination(array $data, int $total, int $limit, int $skip)
    {
        $previous = 0;
        $next     = 0;

        if (isset($data['@odata.nextLink'])) {
            $parts = explode('skip=', $data['@odata.nextLink']);

            if (isset($parts[1])) {
                $previous = $parts[1] - $limit;
                $next     = $parts[1];
            }

            if ($previous < 0) {
                $previous = 0;
            }

            if ($next == $total) {
                $next = 0;
            }
        }

        if ($total > $limit) {
            $previous = $skip - $limit;
        }

        if ($previous < 0) {
            $previous = 0;
        }

        return [
            'previous' => $previous,
            'next'     => $next,
        ];
    }

    /**
     * @param $token
     * @param $id
     * @param $email
     * @return mixed|string
     * @throws IdentityProviderException
     */
    protected function renewExpiringToken($token, $id, $email)
    {
        $oauthClient = $this->getProvider();
        $newToken    = $oauthClient->getAccessToken('refresh_token', ['refresh_token' => $token->refresh_token]);
        $this->storeToken($newToken->getToken(), $newToken->getRefreshToken(), $newToken->getExpires(), $id, $email);

        return $newToken->getToken();
    }

    /**
     * __call catches all requests when no found method is requested.
     * @param  $function  - the verb to execute
     * @param  $args  - array of arguments
     * @return json request
     * @throws Exception
     */
    public function __call($function, $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path    = (isset($args[0])) ? $args[0] : null;
        $data    = (isset($args[1])) ? $args[1] : null;
        $headers = (isset($args[2])) ? $args[2] : null;
        $id      = (isset($args[3])) ? $args[3] : auth()->id();

        if (in_array($function, $options)) {
            return self::guzzle($function, $path, $data, $headers, $id);
        } else {
            //request verb is not in the $options array
            throw new Exception($function.' is not a valid HTTP Verb');
        }
    }

    /**
     * run guzzle to process requested url.
     * @param  $type string
     * @param  $request string
     * @param  $data array
     * @param  array  $headers
     * @param  $id integer
     * @return json object
     */
    protected function guzzle($type, $request, $data = [], $headers = [], $id = null)
    {
        try {
            $client = new Client;

            $mainHeaders = [
                'Authorization' => 'Bearer '.$this->getAccessToken($id),
                'content-type'  => 'application/json',
                'Prefer'        => config('msgraph.preferTimezone'),
            ];

            if (is_array($headers)) {
                $headers = array_merge($mainHeaders, $headers);
            } else {
                $headers = $mainHeaders;
            }

            $response = $client->$type(self::$baseUrl.$request, [
                'headers' => $headers,
                'body'    => json_encode($data),
            ]);

            $responseObject = $response->getBody()->getContents();

            $isJson = $this->isJson($responseObject);

            if ($isJson) {
                return json_decode($responseObject, true);
            }

            return $responseObject;

        } catch (ClientException $e) {
            return json_decode(($e->getResponse()->getBody()->getContents()));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $string
     * @return bool
     */
    protected function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @param $id
     * @return int|mixed|string|null
     */
    protected function getUserId($id = null)
    {
        if ($id === null) {
            $id = auth()->id();
        }

        return $id;
    }

    /**
     * @return GenericProvider
     */
    protected function getProvider()
    {
        //set up the provides loaded values from the config
        return new GenericProvider([
            'clientId'                => config('msgraph.clientId'),
            'clientSecret'            => config('msgraph.clientSecret'),
            'redirectUri'             => config('msgraph.redirectUri'),
            'urlAuthorize'            => config('msgraph.urlAuthorize'),
            'urlAccessToken'          => config('msgraph.urlAccessToken'),
            'urlResourceOwnerDetails' => config('msgraph.urlResourceOwnerDetails'),
            'scopes'                  => config('msgraph.scopes'),
        ]);
    }
}
