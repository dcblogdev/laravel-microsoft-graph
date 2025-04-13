<?php

namespace Dcblogdev\MsGraph;

/*
 * msgraph api documentation can be found at https://developer.msgraph.com/reference
 **/

use Dcblogdev\MsGraph\Events\NewMicrosoft365SignInEvent;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\Resources\Contacts;
use Dcblogdev\MsGraph\Resources\Emails\Emails;
use Dcblogdev\MsGraph\Resources\Files;
use Dcblogdev\MsGraph\Resources\Sites;
use Dcblogdev\MsGraph\Resources\Tasks\TaskLists;
use Dcblogdev\MsGraph\Resources\Tasks\Tasks;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Model\User;
use TestUser;

class MsGraph
{
    public function contacts(): Contacts
    {
        return new Contacts;
    }

    public function emails(): Emails
    {
        return new Emails;
    }

    public function files(): Files
    {
        return new Files;
    }

    public function sites(): Sites
    {
        return new Sites;
    }

    public function tasklists(): TaskLists
    {
        return new TaskLists;
    }

    public function tasks(): Tasks
    {
        return new Tasks;
    }

    protected static User|TestUser|null $user = null;

    protected static string $baseUrl = 'https://graph.microsoft.com/v1.0/';

    protected static string $userModel = '';

    /**
     * @throws Exception
     */
    public function setApiVersion(string $version = '1.0'): static
    {
        self::$baseUrl = match ($version) {
            '1.0' => 'https://graph.microsoft.com/v1.0/',
            'beta' => 'https://graph.microsoft.com/beta/',
            default => throw new Exception("API version $version is not supported!"),
        };

        return $this;
    }

    public function getApiVersion(): string
    {
        return self::$baseUrl;
    }

    public static function setUserModel(string $model): static
    {
        self::$userModel = $model;

        return new static;
    }

    public static function login(User|TestUser|null $user): void
    {
        self::$user = $user;
    }

    public static function getUser(): User|TestUser|null
    {
        return self::$user;
    }

    /**
     * @throws Exception
     */
    public function connect(?string $id = null): Redirector|RedirectResponse
    {
        $id = $this->getUserId($id);

        $provider = $this->getProvider();

        if (! $this->isConnected($id)) {
            $token = $this->getTokenData($id);

            if ($token !== null) {
                if ($token->expires < time()) {
                    $user = (self::$userModel ?: config('auth.providers.users.model'))::find($id);
                    $this->renewExpiringToken($token, $id, $user->email);
                }
            }
        }

        if (request()->has('error')) {
            throw new Exception('Error: '.request('error').'<br/>Description: '.request('error_description'));
        }

        if (! request()->has('code') && ! $this->isConnected($id)) {
            return redirect($provider->getAuthorizationUrl());
        }

        if (request()->has('code')) {

            try {
                $accessToken = $provider->getAccessToken('authorization_code', ['code' => request('code')]);
            } catch (IdentityProviderException $e) {

                $response = $e->getResponseBody();

                $errorMessage = "{$response['error']} {$response['error_description']}\n".
                    'Error Code: '.($response['error_codes'][0] ?? 'N/A')."\n".
                    'More Info: '.($response['error_uri'] ?? 'N/A');

                throw new Exception($errorMessage);
            }

            if (auth()->check()) {
                $this->storeToken(
                    $accessToken->getToken(),
                    $accessToken->getRefreshToken(),
                    $accessToken->getExpires(),
                    $id,
                    auth()->user()->email
                );
            } else {

                $response = Http::withToken($accessToken->getToken())->get(self::$baseUrl.'me');

                event(new NewMicrosoft365SignInEvent([
                    'info' => $response->json(),
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                ]));
            }
        }

        return redirect(config('msgraph.msgraphLandingUri'));
    }

    public function isConnected(?string $id = null): bool
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

    public function disconnect(string $redirectPath = '/', bool $logout = true): RedirectResponse
    {
        if ($logout === true && auth()->check()) {
            auth()->logout();
        }

        return redirect()->away('https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri='.url($redirectPath));
    }

    public function getAccessToken(?string $id = null, bool $redirectWhenNotConnected = true): Application|Redirector|string|RedirectResponse|null
    {
        $token = $this->getTokenData($id);
        $id = $this->getUserId($id);

        if ($this->getUser() === null && $redirectWhenNotConnected) {
            if (! $this->isConnected($id)) {
                return redirect()->away(config('msgraph.redirectUri'));
            }
        }

        if ($token === null) {
            return null;
        }

        if ($token->expires < time() + 300) {
            $user = (self::$userModel ?: config('auth.providers.users.model'))::find($id);

            return $this->renewExpiringToken($token, $id, $user->email);
        }

        return $token->access_token;
    }

    public function getTokenData(?string $id = null): ?MsGraphToken
    {
        $id = $this->getUserId($id);

        if ($id === null) {
            return null;
        }

        return MsGraphToken::where('user_id', $id)->where('refresh_token', '<>', '')->first();
    }

    public function storeToken(string $access_token, string $refresh_token, string $expires, string $id, string $email): MsGraphToken
    {
        return MsGraphToken::updateOrCreate(['user_id' => $id], [
            'user_id' => $id,
            'email' => $email,
            'access_token' => $access_token,
            'expires' => $expires,
            'refresh_token' => $refresh_token,
        ]);
    }

    public function getPagination(array $data, int $total, int $limit, int $skip): array
    {
        $previous = 0;
        $next = 0;

        if (isset($data['@odata.nextLink'])) {
            $parts = explode('skip=', $data['@odata.nextLink']);

            if (isset($parts[1])) {
                $previous = $parts[1] - $limit;
                $next = $parts[1];
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
            'next' => $next,
        ];
    }

    /**
     * @throws Exception
     */
    public function __call(string $function, array $args)
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path = (isset($args[0])) ? $args[0] : '';
        $data = (isset($args[1])) ? $args[1] : [];
        $headers = (isset($args[2])) ? $args[2] : [];
        $id = (isset($args[3])) ? $args[3] : auth()->id();

        if (in_array($function, $options)) {
            return self::guzzle($function, $path, $data, $headers, $id);
        } else {
            // request verb is not in the $options array
            throw new Exception($function.' is not a valid HTTP Verb');
        }
    }

    /**
     * @throws IdentityProviderException
     */
    protected function renewExpiringToken(object $token, string $id, string $email): mixed
    {
        $oauthClient = $this->getProvider();
        $newToken = $oauthClient->getAccessToken('refresh_token', ['refresh_token' => $token->refresh_token]);
        $this->storeToken($newToken->getToken(), $newToken->getRefreshToken(), $newToken->getExpires(), $id, $email);

        return $newToken->getToken();
    }

    /**
     * @throws Exception
     */
    protected function guzzle(string $type, string $request, array $data = [], array $headers = [], int|string|null $id = null): mixed
    {
        try {
            $client = new Client;

            $mainHeaders = [
                'Authorization' => 'Bearer '.$this->getAccessToken($id),
                'content-type' => 'application/json',
                'Prefer' => config('msgraph.preferTimezone'),
            ];

            if (is_array($headers)) {
                $headers = array_merge($mainHeaders, $headers);
            } else {
                $headers = $mainHeaders;
            }

            $response = $client->$type(self::$baseUrl.$request, [
                'headers' => $headers,
                'body' => json_encode($data),
            ]);

            $responseObject = $response->getBody()->getContents();

            $isJson = $this->isJson($responseObject);

            if ($isJson) {
                return json_decode($responseObject, true);
            }

            return $responseObject;

        } catch (ClientException $e) {
            throw new Exception($e->getMessage());
            // return json_decode(($e->getResponse()->getBody()->getContents()));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function isJson(string $string): bool
    {
        return is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

    protected function getUserId(?string $id = null): ?string
    {
        if ($this->getUser() !== null) {
            $id = $this->getUser()->id;
        }

        if ($id === null) {
            $id = auth()->id();
        }

        return $id;
    }

    protected function getProvider(): GenericProvider
    {
        app()->singleton(GenericProvider::class, function () {

            $codeVerifier = bin2hex(random_bytes(32));

            return new GenericProvider([
                'clientId' => config('msgraph.clientId'),
                'clientSecret' => config('msgraph.clientSecret'),
                'redirectUri' => config('msgraph.redirectUri'),
                'urlAuthorize' => config('msgraph.urlAuthorize'),
                'urlAccessToken' => config('msgraph.urlAccessToken'),
                'urlResourceOwnerDetails' => config('msgraph.urlResourceOwnerDetails'),
                'scopes' => config('msgraph.scopes'),
                'code_challenge_method' => 'S256',
                'code_challenge' => rtrim(
                    strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '='
                ),
            ]);
        });

        // You can now resolve GenericProvider from the service container
        return app(GenericProvider::class);
    }
}
