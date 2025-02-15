<?php

use Dcblogdev\MsGraph\Facades\MsGraph;

test('refreshes token when MsGraph is connected', function () {
    // Mock MsGraph::isConnected() to return true
    MsGraph::shouldReceive('isConnected')->once()->andReturn(true);
    MsGraph::shouldReceive('getAccessToken')->once()->with(null, false);

    $this->artisan('msgraph:keep-alive')
        ->expectsOutput('connected')
        ->assertExitCode(0);
});

test('does nothing when MsGraph is not connected', function () {
    // Mock MsGraph::isConnected() to return false
    MsGraph::shouldReceive('isConnected')->once()->andReturn(false);

    $this->artisan('msgraph:keep-alive')
        ->doesntExpectOutput('connected')
        ->assertExitCode(0);
});
