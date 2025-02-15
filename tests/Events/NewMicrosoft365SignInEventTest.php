<?php

use Dcblogdev\MsGraph\Events\NewMicrosoft365SignInEvent;
use Illuminate\Support\Facades\Event;

test('NewMicrosoft365SignInEvent is dispatched with token data', function () {
    Event::fake();

    $tokenData = [
        'accessToken' => 'fake_access_token',
        'refreshToken' => 'fake_refresh_token',
        'expires' => now()->addHour()->timestamp,
        'info' => [
            'mail' => 'test@example.com',
            'displayName' => 'Test User',
        ],
    ];

    // Dispatch the event
    event(new NewMicrosoft365SignInEvent($tokenData));

    // Assert event was dispatched
    Event::assertDispatched(NewMicrosoft365SignInEvent::class, function ($event) use ($tokenData) {
        return $event->token === $tokenData;
    });
});
