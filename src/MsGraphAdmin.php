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
use GuzzleHttp\Exception\GuzzleException;

class MsGraphAdmin
{
    public function calendarEvents(): CalendarEvents
    {
        return new CalendarEvents;
    }

    public function calendars(): Calendars
    {
        return new Calendars;
    }

    public function contacts(): Contacts
    {
        return new Contacts;
    }

    public function emails(): Emails
    {
        return new Emails;
    }

    public function events(): Events
    {
        return new Events;
    }

    public function files(): Files
    {
        return new Files;
    }

    protected static string $baseUrl = 'https://graph.microsoft.com/v1.0/';

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

    public function isConnected(): bool
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

    public function connect(bool $redirect = true): mixed
    {
        $params = [
            'scope' => 'https://graph.microsoft.com/.default',
            'client_id' => config('msgraph.clientId'),
            'client_secret' => config('msgraph.clientSecret'),
            'grant_type' => 'client_credentials',
        ];

        $token = $this->doPost(config('msgraph.tenantUrlAccessToken'), $params);

        if (isset($token->access_token)) {
            $this->storeToken($token->access_token, '', $token->expires_in);
        }

        if ($redirect) {
            return redirect(config('msgraph.msgraphLandingUri'));
        }

        return $token->access_token ?? null;
    }

    public function getAccessToken(bool $returnNullNoAccessToken = false, bool $redirect = false): mixed
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

    public function getTokenData(): ?MsGraphToken
    {
        return MsGraphToken::where('user_id', null)->first();
    }

    protected function storeToken(string $access_token, string $refresh_token, string $expires): MsGraphToken
    {
        // Create or update a new record for admin token
        return MsGraphToken::updateOrCreate(['user_id' => null], [
            'email' => 'application_token', // Placeholder name
            'access_token' => $access_token,
            'expires' => (time() + $expires),
            'refresh_token' => $refresh_token,
        ]);
    }

    /**
     * @throws Exception
     */
    public function __call(string $function, array $args): mixed
    {
        $options = ['get', 'post', 'patch', 'put', 'delete'];
        $path = (isset($args[0])) ? $args[0] : null;
        $data = (isset($args[1])) ? $args[1] : null;

        if (in_array($function, $options)) {
            return self::guzzle($function, $path, $data);
        } else {
            // request verb is not in the $options array
            throw new Exception($function.' is not a valid HTTP Verb');
        }
    }

    protected function isJson(string $data): bool
    {
        return is_array(json_decode($data, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @throws Exception
     */
    protected function guzzle(string $type, string $request, ?array $data = []): mixed
    {
        try {
            $client = new Client;

            $response = $client->$type(self::$baseUrl.$request, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getAccessToken(),
                    'content-type' => 'application/json',
                    'Prefer' => config('msgraph.preferTimezone'),
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

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    protected static function doPost(string $url, array $params): mixed
    {
        try {
            $client = new Client;
            $response = $client->post($url, ['form_params' => $params]);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return json_decode(($e->getResponse()->getBody()->getContents()));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getPagination(array $data, string $top = '0', string $skip = '0'): array
    {
        $total = $data['@odata.count'] ?? 0;

        if (isset($data['@odata.nextLink'])) {
            $parts = parse_url($data['@odata.nextLink']);
            parse_str($parts['query'], $query);

            $top = $query['$top'] ?? 0;
            $skip = $query['$skip'] ?? 0;
        }

        return [
            'total' => $total,
            'top' => $top,
            'skip' => $skip,
        ];
    }
}
