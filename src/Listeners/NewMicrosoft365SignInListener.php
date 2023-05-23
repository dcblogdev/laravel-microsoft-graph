<?php

namespace App\Listeners;

use App\Models\User;
use Dcblogdev\MsGraph\Models\MsGraphToken;
use Illuminate\Support\Facades\Auth;

class NewMicrosoft365SignInListener
{
    public function handle($event)
    {
        $token = MsGraphToken::find($event->token['token_id']);
        $user  = User::firstOrCreate([
            'email' => $event->token['info']->mail,
        ], [
            'name'     => $event->token['info']->displayName,
            'email'    => $event->token['info']->mail,
            'password' => '',
        ]);

        $token->user_id = $user->id;
        $token->save();

        Auth::login($user);
    }
}
