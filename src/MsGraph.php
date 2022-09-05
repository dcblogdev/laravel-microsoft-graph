<?php

namespace Dcblogdev\MsGraph;

/*
 * msgraph api documentation can be found at https://developer.msgraph.com/reference
 **/

use Dcblogdev\MsGraph\Events\NewMicrosoft365SignInEvent;
use Dcblogdev\MsGraph\Facades\MsGraph as Api;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\Resources\Contacts;
use Dcblogdev\MsGraph\Resources\Emails;
use Dcblogdev\MsGraph\Resources\Files;
use Dcblogdev\MsGraph\Resources\Tasks;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
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
        //if no id passed get logged in user
        if ($id == null) {
            $id = auth()->id();
        }

        //set up the provides loaded values from the config
        $provider = new GenericProvider([
            'clientId'                => config('msgraph.clientId'),
            'clientSecret'            => config('msgraph.clientSecret'),
            'redirectUri'             => config('msgraph.redirectUri'),
            'urlAuthorize'            => config('msgraph.urlAuthorize'),
            'urlAccessToken'          => config('msgraph.urlAccessToken'),
            'urlResourceOwnerDetails' => config('msgraph.urlResourceOwnerDetails'),
            'scopes'                  => config('msgraph.scopes'),
        ]);

        //when no code param redirect to Microsoft
        if (!request()->has('code')) {
            return redirect($provider->getAuthorizationUrl());
        } elseif (request()->has('code')) {
            // With the authorization code, we can retrieve access tokens and other data.
            try {
                // Get an access token using the authorization code grant
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => request('code'),
                ]);

                $result = $this->storeToken(
                    $accessToken->getToken(),
                    $accessToken->getRefreshToken(),
                    $accessToken->getExpires(),
                    $id
                );

                //get user details
                $me = Api::get('me', null, [], $id);

                $event = [
                    'token_id' => $result->id,
                    'info'     => $me,
                ];

                if ($event['info']['mail'] === null) {
                    $email = $event['info']['userPrincipalName'];
                } else {
                    $email = $event['info']['mail'];
                }

                //fire event
                event(new NewMicrosoft365SignInEvent($event));

                //find record and add email - not required but useful none the less
                $t        = MsGraphToken::findOrFail($result->id);
                $t->email = $email;
                $t->save();

                return redirect(config('msgraph.msgraphLandingUri'));
            } catch (IdentityProviderException $e) {
                die('error:'.$e->getMessage());
            }
        }
    }

    /**
     * @return object
     */
    public function isConnected($id = null)
    {
        return $this->getTokenData($id) == null ? false : true;
    }

    /**
     * logout of application and Microsoft 365, redirects back to the provided path.
     * @param  string  $redirectPath
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect($redirectPath = '/', $logout = true, $id = null)
    {
        $id = ($id) ? $id : auth()->id();
        $token = MsGraphToken::where('user_id', $id)->latest()->first();

        if ($token != null) {
            $token->delete();
        }

        //if logged in and $logout is set to true then logout
        if ($logout == true && auth()->check()) {
            auth()->logout();
        }

        return redirect()->away('https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri='.url($redirectPath));
    }

    /**
     * Return authenticated access token or request new token when expired.
     * @param  $id integer - id of the user
     * @param  $returnNullNoAccessToken null when set to true return null
     * @return return string access token
     */
    public function getAccessToken($id = null, $returnNullNoAccessToken = null)
    {
        //use id if passed otherwise use logged in user
        $id    = ($id) ? $id : auth()->id();
        $token = MsGraphToken::where('user_id', $id)->latest()->first();

        // Check if tokens exist otherwise run the oauth request
        if (!isset($token->access_token)) {
            //don't redirect simply return null when no token found with this option
            if ($returnNullNoAccessToken == true) {
                return null;
            }

            return redirect(config('msgraph.redirectUri'));
        }

        // Check if token is expired
        // Get current time + 5 minutes (to allow for time differences)
        $now = time() + 300;
        if ($token->expires <= $now) {
            // Token is expired (or very close to it) so let's refresh

            // Initialize the OAuth client
            $oauthClient = new GenericProvider([
                'clientId'                => config('msgraph.clientId'),
                'clientSecret'            => config('msgraph.clientSecret'),
                'redirectUri'             => config('msgraph.redirectUri'),
                'urlAuthorize'            => config('msgraph.urlAuthorize'),
                'urlAccessToken'          => config('msgraph.urlAccessToken'),
                'urlResourceOwnerDetails' => config('msgraph.urlResourceOwnerDetails'),
                'scopes'                  => config('msgraph.scopes'),
            ]);

            $newToken = $oauthClient->getAccessToken('refresh_token', ['refresh_token' => $token->refresh_token]);

            // Store the new values
            $this->storeToken($newToken->getToken(), $newToken->getRefreshToken(), $newToken->getExpires(), $id);

            return $newToken->getToken();
        } else {
            // Token is still valid, just return it
            return $token->access_token;
        }
    }

    /**
     * @param  $id  - integar id of user
     * @return object
     */
    public function getTokenData($id = null)
    {
        $id = ($id) ? $id : auth()->id();
        return MsGraphToken::where('user_id', $id)->latest()->first();
    }

    /**
     * Store token.
     * @param  $access_token string
     * @param  $refresh_token string
     * @param  $expires string
     * @param  $id integer
     * @return object
     */
    protected function storeToken($access_token, $refresh_token, $expires, $id)
    {
        //cretate a new record or if the user id exists update record
        return MsGraphToken::updateOrCreate(['user_id' => $id], [
            'user_id'       => $id,
            'access_token'  => $access_token,
            'expires'       => $expires,
            'refresh_token' => $refresh_token,
        ]);
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

    protected function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
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
            throw new Exception($e->getResponse()->getBody()->getContents());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
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
}
