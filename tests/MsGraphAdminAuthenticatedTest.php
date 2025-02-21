<?php

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Dcblogdev\MsGraph\MsGraphAdminAuthenticated;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(MsGraphAdminAuthenticated::class)->get('/test-route', function () {
        return response()->json(['message' => 'Access granted']);
    });
});

test('redirects to MsGraphAdmin::connect() when not connected', function () {
    // Mock MsGraphAdmin::isConnected() to return false
    MsGraphAdmin::shouldReceive('isConnected')->once()->andReturn(false);
    MsGraphAdmin::shouldReceive('connect')->once()->andReturn(redirect('https://login.microsoftonline.com'));

    $response = $this->get('/test-route');

    $response->assertRedirect('https://login.microsoftonline.com');
});

test('allows request when MsGraphAdmin is connected', function () {
    // Mock MsGraphAdmin::isConnected() to return true
    MsGraphAdmin::shouldReceive('isConnected')->once()->andReturn(true);

    $response = $this->get('/test-route');

    $response->assertOk()
        ->assertJson(['message' => 'Access granted']);
});
