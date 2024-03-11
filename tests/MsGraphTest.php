<?php

use Dcblogdev\MsGraph\Facades\MsGraph as MsGraphFacade;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\MsGraph;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
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

})->throws(IdentityProviderException::class);

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
