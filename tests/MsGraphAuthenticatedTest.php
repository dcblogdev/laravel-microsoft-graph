<?php

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\MsGraphAuthenticated;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(MsGraphAuthenticated::class)->get('/test-route', function () {
        return response()->json(['message' => 'Access granted']);
    });
});

test('redirects to MsGraph::connect() when not connected', function () {
    // Mock MsGraph::isConnected() to return false
    MsGraph::shouldReceive('isConnected')->once()->andReturn(false);
    MsGraph::shouldReceive('connect')->once()->andReturn(redirect('https://login.microsoftonline.com'));

    $response = $this->get('/test-route');

    $response->assertRedirect('https://login.microsoftonline.com');
});

test('allows request when MsGraph is connected', function () {
    // Mock MsGraph::isConnected() to return true
    MsGraph::shouldReceive('isConnected')->once()->andReturn(true);

    $response = $this->get('/test-route');

    $response->assertOk()
        ->assertJson(['message' => 'Access granted']);
});
