<?php

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;

test('refreshes admin token when MsGraphAdmin is connected', function () {
    // Mock MsGraphAdmin::isConnected() to return true
    MsGraphAdmin::shouldReceive('isConnected')->once()->andReturn(true);
    MsGraphAdmin::shouldReceive('getAccessToken')->once()->with(true);

    $this->artisan('msgraphadmin:keep-alive')
        ->expectsOutput('connected')
        ->assertExitCode(0);
});

test('does nothing when MsGraphAdmin is not connected', function () {
    // Mock MsGraphAdmin::isConnected() to return false
    MsGraphAdmin::shouldReceive('isConnected')->once()->andReturn(false);

    $this->artisan('msgraphadmin:keep-alive')
        ->doesntExpectOutput('connected')
        ->assertExitCode(0);
});
