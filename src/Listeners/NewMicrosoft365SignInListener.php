<?php

namespace App\Listeners;

use App\Models\User;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Illuminate\Support\Facades\Auth;

class NewMicrosoft365SignInListener
{
    public function handle($event)
    {
        $tokenId = $event->token['token_id'];
        $token   = MsGraphToken::find($tokenId)->first();

        if ($token->user_id == null) {
            $user = User::create([
                'name'     => $event->token['info']['displayName'],
                'email'    => $event->token['info']['mail'],
                'password' => '',
            ]);

            $token->user_id = $user->id;
            $token->save();

            Auth::login($user);
        } else {
            $user = User::findOrFail($token->user_id);
            $user->save();

            Auth::login($user);
        }
    }
}
