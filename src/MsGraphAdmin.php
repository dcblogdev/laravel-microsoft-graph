<?php

namespace Daveismyname\MsGraph;

/**
* msgraph api documenation can be found at https://developer.msgraph.com/reference
**/

use Daveismyname\MsGraph\Models\MsGraphToken;

use Daveismyname\MsGraph\AdminResources\Contacts;
use Daveismyname\MsGraph\AdminResources\Emails;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Exception;

class MsGraphAdmin
{
    public function contacts()
    {
        return new Contacts();
    }

    public function emails()
    {
        return new Emails();
    }

    /**
     * Set the base url that all API requests use
     * @var string
     */
    protected static $baseUrl = 'https://graph.microsoft.com/beta/';

    /**
     * __call catches all requests when no founf method is requested
     * @param  $function - the verb to execute
     * @param  $args - array of arguments
     * @return gizzle request
     */
    public function __call($function, $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path = (isset($args[0])) ? $args[0] : null;
        $data = (isset($args[1])) ? $args[1] : null;

        if (in_array($function, $options)) {
            return self::guzzle($function, $path, $data);
        } else {
            //request verb is not in the $options array
            throw new Exception($function.' is not a valid HTTP Verb');
        }
    }

    /**
     * Make a connection or return a token where it's valid
     * @return mixed
     */
    public function connect()
    {
        //when no code param redirect to Microsoft
        if (!request()->has('tenant')) {

            $url = config('msgraph.tenantUrlAuthorize') . '?' . http_build_query([
                'client_id' => config('msgraph.clientId'),
                'redirect_uri' => config('msgraph.redirectUri')
            ]);

            return redirect()->away($url);

        } elseif (request()->has('tenant')) {
            
            // With the authorization code, we can retrieve access tokens and other data.
            try {
                
                $params = [
                    'scope' => 'https://graph.microsoft.com/.default',
                    'client_id' => config('msgraph.clientId'),
                    'client_secret' => config('msgraph.clientSecret'),
                    'grant_type' => 'client_credentials'
                ];

                $token = $this->dopost(config('msgraph.tenantUrlAccessToken'), $params);

                $this->storeToken($token->access_token, '', $token->expires_in);

                return redirect(config('msgraph.msgraphLandingUri'));

            } catch (Exception $e) {
                die('error 90: '.$e->getMessage());
            }

        }
    }

    /**
     * Return authenticated access token or request new token when expired
     * @param  $id integer - id of the user
     * @param  $returnNullNoAccessToken null when set to true return null
     * @return return string access token
     */
    public function getAccessToken($returnNullNoAccessToken = null)
    {
        //use id if passed otherwise use logged in user
        $token = MsGraphToken::where('user_id', null)->first();

        // Check if tokens exist otherwise run the oauth request
        if (! isset($token->access_token)) {

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

            $params = [
                'grant_type' => 'authorization_code',
                'scope' => 'https://graph.microsoft.com/.default',
                'client_id' => config('msgraph.clientId'),
                'client_secret' => config('msgraph.clientSecret'),
                'grant_type' => 'client_credentials'
            ];

            $token = $this->dopost(config('msgraph.tenantUrlAccessToken'), $params);

            $newToken = $this->storeToken($token->access_token, '', $token->expires_in);

            return $newToken->access_token;

        } else {
            // Token is still valid, just return it
            return $token->access_token;
        }
    }

    /**
     * @param  $id - integar id of user
     * @return object
     */
    public function getTokenData()
    {
        return MsGraphToken::where('user_id', null)->first();
    }

    /**
     * Store token
     * @param  $access_token string
     * @param  $refresh_token string
     * @param  $expires string
     * @param  $id integer
     * @return object
     */
    protected function storeToken($access_token, $refresh_token, $expires)
    {
        //cretate a new record or if the user id exists update record
        return MsGraphToken::updateOrCreate(['user_id' => null], [
            'access_token'  => $access_token,
            'expires'       => $expires,
            'refresh_token' => $refresh_token
        ]);
    }

    /**
     * run guzzle to process requested url
     * @param  $type string
     * @param  $request string
     * @param  $data array
     * @param  $id integer
     * @return json object
     */
    protected function guzzle($type, $request, $data = [])
    {
        try {
            $client = new Client;

            $response = $client->$type(self::$baseUrl.$request, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getAccessToken(),
                    'content-type' => 'application/json',
                    'Prefer' => config('msgraph.preferTimezone')
                ],
                'body' => json_encode($data),
            ]);
            
            if ($resonse == null) {
                return null;
            }

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            return json_decode(($e->getResponse()->getBody()->getContents()));
        } catch (Exception $e) {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }

    protected static function dopost($url, $params)
    {
        try {
            $client = new Client;
            $response = $client->post($url, ['form_params' => $params]);
            
            if ($resonse == null) {
                return null;
            }

            return json_decode($response->getBody()->getContents());

        } catch (ClientException $e) {
            return json_decode(($e->getResponse()->getBody()->getContents()));
        } catch (Exception $e) {
           return json_decode($e->getResponse(), true);
        }
    }

    /**
     * return tarray containing total, top and skip params
     * @param  $data array
     * @param  $top  integer
     * @param  $skip integer
     * @return array
     */
    public function getPagination($data, $top, $skip)
    {
        if (! is_array($data))
        {
            dd($data);
        }
        
        $total = isset($data['@odata.count']) ? $data['@odata.count'] : 0;

        if (isset($data['@odata.nextLink'])) {

            $parts = parse_url($data['@odata.nextLink']);
            parse_str($parts['query'], $query);

            $top = isset($query['$top']) ? $query['$top'] : 0;
            $skip = isset($query['$skip']) ? $query['$skip'] : 0;

        } else {
            $top = 0;
            $skip = 0;
        }

        return [
            'total' => $total,
            'top' => $top,
            'skip' => $skip
        ];
    }
}
