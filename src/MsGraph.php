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

    protected static $baseUrl = 'https://graph.microsoft.com/beta/';

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

    public function connect()
    {
        $provider = new GenericProvider([
            'clientId'                => config('msgraph.clientId'),
            'clientSecret'            => config('msgraph.clientSecret'),
            'redirectUri'             => config('msgraph.redirectUri'),
            'urlAuthorize'            => config('msgraph.urlAuthorize'),
            'urlAccessToken'          => config('msgraph.urlAccessToken'),
            'urlResourceOwnerDetails' => config('msgraph.urlResourceOwnerDetails'),
            'scopes'                  => config('msgraph.scopes')
        ]);

        if (!request()->has('code')) {

            return redirect($provider->getAuthorizationUrl());

        } elseif (request()->has('code')) {

            // With the authorization code, we can retrieve access tokens and other data.
            try {
                // Get an access token using the authorization code grant
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => request('code')
                ]);

                $result = $this->storeToken($accessToken->getToken(), $accessToken->getRefreshToken(), $accessToken->getExpires());

                //get user details
                $me = Api::get('me');

                $t = MsGraphToken::findOrFail($result->id);
                $t->email = $me['mail'];
                $t->save();

                return redirect(config('msgraph.msgraphLandingUri'));

            } catch (IdentityProviderException $e) {
                die('error:'.$e->getMessage());
            }

        }
    }

    public function getAccessToken($id = null)
    {
        $id    = ($id) ? $id : auth()->id();
        $token = MsGraphToken::where('user_id', $id)->first();

        // Check if tokens exist otherwise run the oauth request
        if (!isset($token->access_token)) {
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
            $this->storeToken($newToken->getToken(), $newToken->getRefreshToken(), $newToken->getExpires());

            return $newToken->getToken();

        } else {
            // Token is still valid, just return it
            return $token->access_token;
        }
    }

    protected function storeToken($access_token, $refresh_token, $expires)
    {
        //cretate a new record or if the user id exists update record
        return MsGraphToken::updateOrCreate(['user_id' => auth()->id()], [
            'user_id'       => auth()->id(),
            'access_token'  => $access_token,
            'expires'       => $expires,
            'refresh_token' => $refresh_token
        ]);
    }

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

            return json_decode($response->getBody()->getContents(), true);

        } catch (Exception $e) {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }

}
