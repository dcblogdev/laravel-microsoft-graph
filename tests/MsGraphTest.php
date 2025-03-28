<?php

use Dcblogdev\MsGraph\Facades\MsGraph as MsGraphFacade;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\MsGraph;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Mockery\MockInterface;

beforeEach(function () {
    $this->msGraphMock = Mockery::mock(MsGraph::class);
});

test('can initialise', function () {
    $this->assertInstanceOf(MsGraph::class, $this->msGraphMock);
});

test('can get default version', function () {
    MsGraphFacade::setApiVersion();

    expect(MsGraphFacade::getApiVersion())
        ->toBeString()
        ->toContain('https://graph.microsoft.com/v1.0/');
});

test('can get version 1.0', function () {
    MsGraphFacade::setApiVersion('1.0');

    expect(MsGraphFacade::getApiVersion())
        ->toBeString()
        ->toContain('https://graph.microsoft.com/v1.0/');
});

test('can get beta version', function () {
    MsGraphFacade::setApiVersion('beta');

    expect(MsGraphFacade::getApiVersion())
        ->toBeString()
        ->toContain('https://graph.microsoft.com/beta/');
});

test('calling none supported versions throws exception', function () {
    MsGraphFacade::setApiVersion('0.1');
})->throws(Exception::class);

test('connect redirects to microsoft', function () {

    $response = MsGraphFacade::connect();

    expect($response->getTargetUrl())
        ->toBeString()
        ->toContain('https://login.microsoftonline.com/common/oauth2/v2.0/authorize');
});

test('connect throws exception upon receiving an error', function () {

    $this->get('?error=sample_error');

    MsGraphFacade::connect();

})->throws(Exception::class);

test('connect with invalid code throws IdentityProviderException', function () {

    $this->get('?code=sample');

    MsGraphFacade::connect();

})->throws(Exception::class);

test('can connect with valid code', function () {

    $this->mock(GenericProvider::class, function (MockInterface $mock) {
        $mockAccessToken = new AccessToken(['access_token' => 'mock_token']);
        $mock->shouldReceive('getAccessToken')
            ->with('authorization_code', ['code' => 'sample'])
            ->once()
            ->andReturn($mockAccessToken);
    });

    // Simulate a GET request with the 'code' query parameter
    $this->get('?code=sample');

    $response = MsGraphFacade::connect();

    expect($response)->toBeInstanceOf(Redirector::class);
})->skip('Need to figure out how to mock the GenericProvider class');

test('is connected returns false when no valid token exists', function () {
    $response = MsGraphFacade::isConnected();

    expect($response)
        ->toBeBool()
        ->toBeFalse();
});

test('is connected returns true when a valid token exists', function () {
    $userId = 1;
    MsGraphToken::create([
        'user_id' => $userId,
        'access_token' => 'ghgh4h22',
        'refresh_token' => 'rhrh4h22',
        'expires' => strtotime('+1 day'),
    ]);

    $response = MsGraphFacade::isConnected($userId);

    expect($response)
        ->toBeBool()
        ->toBeTrue();
});

test('is connected returns false when token expires', function () {
    $userId = 1;
    MsGraphToken::create([
        'user_id' => $userId,
        'access_token' => 'ghgh4h22',
        'refresh_token' => 'rhrh4h22',
        'expires' => strtotime('-1 day'),
    ]);

    $response = MsGraphFacade::isConnected($userId);

    expect($response)
        ->toBeBool()
        ->toBeFalse();
});

test('is redirected on disconnection', function () {
    $userId = 1;
    MsGraphToken::create([
        'user_id' => $userId,
        'access_token' => 'ghgh4h22',
        'expires' => strtotime('+1 day'),
    ]);

    $connect = MsGraphFacade::disconnect($redirectPath = '/', $logout = true);

    $this->assertInstanceOf(RedirectResponse::class, $connect);
});

test('redirected token when token has expired and redirectWhenNotConnected is true', function () {
    $response = MsGraphFacade::getAccessToken(1, true);
    expect($response)->toBeInstanceOf(RedirectResponse::class);
});

test('returns null when token has expired and redirectWhenNotConnected is false', function () {
    $userId = 1;
    $response = MsGraphFacade::getAccessToken($userId, false);
    expect($response)->toBeNull();
});

test('can store token data', function () {

    $id = 1;
    $email = 'user@demo.com';
    $accessToken = 'token';
    $refreshToken = 'refresh-token';
    $expires = strtotime('+1 day');

    MsGraphFacade::storeToken($accessToken, $refreshToken, $expires, $id, $email);

    $this->assertDatabaseHas('ms_graph_tokens', [
        'user_id' => $id,
        'email' => $email,
        'access_token' => $accessToken,
        'expires' => $expires,
        'refresh_token' => $refreshToken,
    ]);
});

class TestUser extends Authenticatable
{
    protected $fillable = ['id', 'email'];
}

test('can login and set user', function () {
    $user = new TestUser(['id' => 1, 'email' => 'test@example.com']);

    MsGraphFacade::login($user);

    expect(MsGraphFacade::getUser())->toBe($user);
});

test('getUser returns null when no user is logged in', function () {
    MsGraphFacade::login(null); // Reset the user
    expect(MsGraphFacade::getUser())->toBeNull();
});

test('getAccessToken uses logged in user when available', function () {
    $user = new TestUser(['id' => 1, 'email' => 'test@example.com']);

    MsGraphFacade::login($user);

    MsGraphToken::create([
        'user_id' => $user->id,
        'access_token' => 'test_token',
        'refresh_token' => 'refresh_token',
        'expires' => strtotime('+1 day'),
    ]);

    $token = MsGraphFacade::getAccessToken();

    expect($token)->toBe('test_token');
});

test('getUserId returns logged in user id when available', function () {
    $user = new TestUser(['id' => 1, 'email' => 'test@example.com']);

    MsGraphFacade::login($user);

    $reflection = new ReflectionClass(MsGraphFacade::getFacadeRoot());
    $method = $reflection->getMethod('getUserId');
    $method->setAccessible(true);

    $userId = $method->invoke(MsGraphFacade::getFacadeRoot());

    expect($userId)->toBe('1');
});

test('getUserId falls back to auth id when no user is logged in', function () {
    MsGraphFacade::login(null); // Reset the user

    $user = new TestUser(['id' => 2, 'email' => 'test2@example.com']);
    Auth::shouldReceive('id')->andReturn(2);

    $reflection = new ReflectionClass(MsGraphFacade::getFacadeRoot());
    $method = $reflection->getMethod('getUserId');
    $method->setAccessible(true);

    $userId = $method->invoke(MsGraphFacade::getFacadeRoot());

    expect($userId)->toBe('2');
});

test('getAccessToken redirects when user is not connected and redirectWhenNotConnected is true', function () {
    config(['msgraph.redirectUri' => 'http://example.com/redirect']);

    MsGraphFacade::login(null); // Reset the user
    Auth::shouldReceive('id')->andReturn(null);

    $response = MsGraphFacade::getAccessToken(null, true);

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe('http://example.com/redirect');
});
