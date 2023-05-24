<?php

namespace Dcblogdev\MsGraph;

/*
* msgraph api documenation can be found at https://developer.msgraph.com/reference
**/

use Dcblogdev\MsGraph\AdminResources\CalendarEvents;
use Dcblogdev\MsGraph\AdminResources\Calendars;
use Dcblogdev\MsGraph\AdminResources\Contacts;
use Dcblogdev\MsGraph\AdminResources\Emails;
use Dcblogdev\MsGraph\AdminResources\Events;
use Dcblogdev\MsGraph\AdminResources\Files;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class MsGraphAdmin
{
    public function calendarEvents()
    {
        return new CalendarEvents();
    }

    public function calendars()
    {
        return new Calendars();
    }

    public function contacts()
    {
        return new Contacts();
    }

    public function emails()
    {
        return new Emails();
    }

    public function events()
    {
        return new Events();
    }

    public function files()
    {
        return new Files();
    }

    /**
     * Set the base url that all API requests use.
     * @var string
     */
    protected static $baseUrl = 'https://graph.microsoft.com/v1.0/';

    /**
     * @return bool
     */
    public function isConnected()
    {
       $token = $this->getTokenData();

        if ($token === null) {
            return false;
        }

        if ($token->expires < time()) {
            return false;
        }

        return true;
    }

    /**
     * Make a connection or return a token where it's valid.
     * @return mixed
     */
    public function connect($redirect = true)
    {
        $params = [
            'scope'         => 'https://graph.microsoft.com/.default',
            'client_id'     => config('msgraph.clientId'),
            'client_secret' => config('msgraph.clientSecret'),
            'grant_type'    => 'client_credentials',
        ];

        $token = $this->dopost(config('msgraph.tenantUrlAccessToken'), $params);

        if (isset($token->access_token)) {
            $this->storeToken($token->access_token, '', $token->expires_in);
        }

        if ($redirect) {
            return redirect(config('msgraph.msgraphLandingUri'));
        }

        return $token->access_token ?? null;
    }

    /**
     * Return authenticated access token or request new token when expired.
     * @param  $id integer - id of the user
     * @param  $returnNullNoAccessToken null when set to true return null
     * @return return string access token
     */
    public function getAccessToken($returnNullNoAccessToken = false, $redirect = false)
    {
        // Admin token will be stored without user_id
        $token = MsGraphToken::where('user_id', null)->first();

        // Check if tokens exist otherwise run the oauth request
        if (! isset($token->access_token)) {
            // Don't request new token, simply return null when no token found with this option
            if ($returnNullNoAccessToken) {
                return null;
            }

            return $this->connect($redirect);
        }

        $now = now()->addMinutes(5);

        if ($token->expires < $now) {
            return $this->connect($redirect);
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
     * Store token.
     * @param  $access_token string
     * @param  $refresh_token string
     * @param  $expires string
     * @param  $id integer
     * @return object
     */
    protected function storeToken($access_token, $refresh_token, $expires)
    {
        //Create or update a new record for admin token
        return MsGraphToken::updateOrCreate(['user_id' => null], [
            'email'         => 'application_token', // Placeholder name
            'access_token'  => $access_token,
            'expires'       => (time() + $expires),
            'refresh_token' => $refresh_token,
        ]);
    }

    /**
     * __call catches all requests when no founf method is requested.
     * @param  $function - the verb to execute
     * @param  $args - array of arguments
     * @return json request
     */
    public function __call($function, $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path    = (isset($args[0])) ? $args[0] : null;
        $data    = (isset($args[1])) ? $args[1] : null;

        if (in_array($function, $options)) {
            return self::guzzle($function, $path, $data);
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
                    'content-type'  => 'application/json',
                    'Prefer'        => config('msgraph.preferTimezone'),
                ],
                'body' => json_encode($data),
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

    protected static function dopost($url, $params)
    {
        try {
            $client   = new Client;
            $response = $client->post($url, ['form_params' => $params]);

            if ($response == null) {
                return null;
            }

            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return json_decode(($e->getResponse()->getBody()->getContents()));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * return tarray containing total, top and skip params.
     * @param  $data array
     * @param  $top  integer
     * @param  $skip integer
     * @return array
     */
    public function getPagination($data, $top, $skip)
    {
        if (! is_array($data)) {
            dd($data);
        }

        $total = isset($data['@odata.count']) ? $data['@odata.count'] : 0;

        if (isset($data['@odata.nextLink'])) {
            $parts = parse_url($data['@odata.nextLink']);
            parse_str($parts['query'], $query);

            $top  = isset($query['$top']) ? $query['$top'] : 0;
            $skip = isset($query['$skip']) ? $query['$skip'] : 0;
        } else {
            $top  = 0;
            $skip = 0;
        }

        return [
            'total' => $total,
            'top'   => $top,
            'skip'  => $skip,
        ];
    }
}
