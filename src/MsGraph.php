<?php

namespace DaveismynameLaravel\MsGraph;

/**
* msgraph api documenation can be found at https://developer.msgraph.com/reference
**/

use DaveismynameLaravel\MsGraph\Facades\MsGraph as Api;
use DaveismynameLaravel\MsGraph\Api\Contacts;
use DaveismynameLaravel\MsGraph\Api\Emails;
use DaveismynameLaravel\MsGraph\Models\MsGraphToken;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use GuzzleHttp\Client;
use Exception;

class MsGraph
{
    use Contacts;
    use Emails;

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
        $id = (isset($args[2])) ? $args[2] : auth()->id();

        if (in_array($function, $options)) {
            return self::guzzle($function, $path, $data, $id);
        } else {
            //request verb is not in the $options array
            throw new Exception($function.' is not a valid HTTP Verb');
        }
    }

    /**
     * Make a connection or return a token where it's valid
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
            'scopes'                  => config('msgraph.scopes')
        ]);

        //when no code param redirect to Microsoft
        if (!request()->has('code')) {

            return redirect($provider->getAuthorizationUrl());

        } elseif (request()->has('code')) {

            // With the authorization code, we can retrieve access tokens and other data.
            try {
                // Get an access token using the authorization code grant
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => request('code')
                ]);

                $result = $this->storeToken($accessToken->getToken(), $accessToken->getRefreshToken(), $accessToken->getExpires(), $id);

                //get user details
                $me = Api::get('me');

                //find record and add email - not required but useful none the less
                $t = MsGraphToken::findOrFail($result->id);
                $t->email = $me['mail'];
                $t->save();

                return redirect(config('msgraph.msgraphLandingUri'));

            } catch (IdentityProviderException $e) {
                die('error:'.$e->getMessage());
            }

        }
    }

    /**
     * Return authenticated access token or request new token when expired
     * @param  $id integer - id of the user
     * @param  $returnNullNoAccessToken null when set to true return null
     * @return return string access token
     */
    public function getAccessToken($id = null, $returnNullNoAccessToken = null)
    {
        //use id if passed otherwise use logged in user
        $id    = ($id) ? $id : auth()->id();
        $token = MsGraphToken::where('user_id', $id)->first();

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

            // Initialize the OAuth client
            $oauthClient = new GenericProvider([
                'clientId'                => config('msgraph.clientId'),
                'clientSecret'            => config('msgraph.clientSecret'),
                'redirectUri'             => config('msgraph.redirectUri'),
                'urlAuthorize'            => config('msgraph.urlAuthorize'),
                'urlAccessToken'          => config('msgraph.urlAccessToken'),
                'urlResourceOwnerDetails' => config('msgraph.urlResourceOwnerDetails'),
                'scopes'                  => config('msgraph.scopes')
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
     * @param  $id - integar id of user
     * @return object
     */
    public function getTokenData($id = null)
    {
        $id = ($id) ? $id : auth()->id();
        return MsGraphToken::where('user_id', $id)->first();
    }

    /**
     * Store token
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
    protected function guzzle($type, $request, $data = [], $id)
    {
        try {
            $client = new Client;

            $response = $client->$type(self::$baseUrl.$request, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getAccessToken($id),
                    'content-type' => 'application/json',
                    'Prefer' => config('msgraph.preferTimezone')
                ],
                'body' => json_encode($data),
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (Exception $e) {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }

}
