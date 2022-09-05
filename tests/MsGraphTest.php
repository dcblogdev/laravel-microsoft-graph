<?php

use Dcblogdev\MsGraph\Facades\MsGraph as MsGraphFacade;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\MsGraph;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

beforeEach(function () {
    $this->msGraphMock = Mockery::mock(MsGraph::class);
});

test('can initalise', function () {
    $this->assertInstanceOf(MsGraph::class, $this->msGraphMock);
});

test('redirected when connect is called', function () {
    $connect = MsGraphFacade::connect();

    $this->assertInstanceOf(RedirectResponse::class, $connect);
});

test('is connected returns false when no data in db', function () {
    $connect = MsGraphFacade::isConnected();

    expect($connect)->toBeFalse();
});

test('is connected returns true when data exists in db', function () {
    $userId = 1;
    MsGraphToken::create([
        'user_id'      => $userId,
        'access_token' => 'ghgh4h22',
        'expires'      => strtotime('+1 day'),
    ]);

    $connect = MsGraphFacade::isConnected($userId);

    expect($connect)->toBeTrue();
});

test('is redirected to logout', function () {
    $userId = 1;
    MsGraphToken::create([
        'user_id'      => $userId,
        'access_token' => 'ghgh4h22',
        'expires'      => strtotime('+1 day'),
    ]);

    $this->assertDatabaseCount('ms_graph_tokens', 1);

    $connect = MsGraphFacade::disconnect($redirectPath = '/', $logout = true, $userId);

    $this->assertDatabaseCount('ms_graph_tokens', 0);

    $this->assertInstanceOf(RedirectResponse::class, $connect);
});

test('get access token when exists', function () {
    $userId = 1;
    MsGraphToken::create([
        'user_id'      => $userId,
        'access_token' => 'ghgh4h22',
        'expires'      => strtotime('+1 day'),
    ]);

    $response = MsGraphFacade::getAccessToken($userId);

    $this->assertSame('ghgh4h22', $response);
});

test('get null token when token has expired and returnNullNoAccessToken is null', function () {
    $response = MsGraphFacade::getAccessToken(1, true);

    $this->assertSame(null, $response);
});

test('redirected token when token has expired and returnNullNoAccessToken is null', function () {
    $response = MsGraphFacade::getAccessToken(1, false);

    $this->assertInstanceOf(Redirector::class, $response);
});

test('redirected when token has expired and returnNullNoAccessToken is null', function () {
    $userId   = 1;
    $response = MsGraphFacade::getAccessToken($userId, true);

    $this->assertSame(null, $response);
});

