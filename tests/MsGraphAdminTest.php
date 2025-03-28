<?php

use Dcblogdev\MsGraph\Facades\MsGraphAdmin as MsGraphAdminFacade;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Dcblogdev\MsGraph\MsGraphAdmin;

beforeEach(function () {
    $this->msGraphAdminMock = Mockery::mock(MsGraphAdmin::class);
});

test('can initalise', function () {
    $this->assertInstanceOf(MsGraphAdmin::class, new MsGraphAdmin);
});

test('can refresh token', function () {
    MsGraphToken::create([
        'user_id' => null,
        'access_token' => 'ghgh4h22',
        'expires' => strtotime('+1 day'),
    ]);

    $this->artisan('msgraphadmin:keep-alive')
        ->expectsOutput('connected');
});

test('is connected returns false when no data in db', function () {
    $connect = MsGraphAdminFacade::isConnected();

    expect($connect)->toBeFalse();
});

test('is connected returns true when data exists in db', function () {

    MsGraphToken::create([
        'user_id' => null,
        'access_token' => 'ghgh4h22',
        'expires' => strtotime('+1 day'),
    ]);

    $connect = MsGraphAdminFacade::isConnected();

    expect($connect)->toBeTrue();
});
