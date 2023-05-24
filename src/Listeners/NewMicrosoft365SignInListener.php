<?php

namespace App\Listeners;

use App\Models\User;
use Dcblogdev\MsGraph\MsGraph;
use Illuminate\Support\Facades\Auth;

class NewMicrosoft365SignInListener
{
    public function handle($event)
    {
        $user  = User::firstOrCreate([
            'email' => $event->token['info']['mail'],
        ], [
            'name'     => $event->token['info']['displayName'],
            'email'    => $event->token['info']['mail'] ?? $event->token['info']['userPrincipalName'],
            'password' => '',
        ]);

        (new MsGraph())->storeToken(
            $event->token['accessToken'],
            $event->token['refreshToken'],
            $event->token['expires'],
            $user->id,
            $user->email
        );

        Auth::login($user);
    }
}