---
title: Login with MsGraph
---

This guide will show a process of using MsGraph an authentication service for a fresh Laravel install. No Auth package will be installed and no user authentication system will be built, instead, the entire authentication system will be offloaded to Microsoft Graph.

This means all users will already be connected to Microsoft Graph and all login, 2FA, and password reset will be handled by Microsoft Graph entirely. Your application can then allow logins using Microsoft Graph.

MsGraph will tie into Laravel's Auth system allowing the usage of @auth blade directives and Auth:: calls.

## Install the package
See **Install** page for the full installation instructions

Set the .env variables: make sure to fill these in

```php
MSGRAPH_CLIENT_ID=
MSGRAPH_SECRET_ID=
MSGRAPH_TENANT_ID=

MSGRAPH_OAUTH_URL=https://project.com/connect
MSGRAPH_LANDING_URL=https://project.com/app
MSGRAPH_PREFER_TIMEZONE='outlook.timezone="Europe/London"'
```

## publishing the config:

```php
php artisan vendor:publish --provider="Dcblogdev\MsGraph\MsGraphServiceProvider" --tag="config"
```

## Publish the migrations

```php
php artisan vendor:publish --provider="Dcblogdev\MsGraph\MsGraphServiceProvider" --tag="migrations"
```

## Publish the listener 
this is required for logging in 

```php
php artisan vendor:publish --provider="Dcblogdev\MsGraph\MsGraphServiceProvider" --tag="Listeners"
```

## NewMicrosoft365 SignIn Listener

This will publish the following code into `app/Listeners/NewMicrosoft365SignInListener.php` file contain:

```php
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
```

This runs when a successful login is made from the package, this code can be changed to suit your needs. A new user will be created in the user's table if they do not exist otherwise the user instance will be returned. The MsGraph token will be linked to the user and then logged in using `Auth::login($user)`. From this point there normal Laravel Auth helpers/blade directives are available. 

Record the listeners in EventServiceProvider

Import the 2 events

```php
use App\Listeners\NewMicrosoft365SignInListener;
use Dcblogdev\MsGraph\Events\NewMicrosoft365SignInEvent;
```

In the boot method add:

```php
public function boot()
{
    Event::listen(
        NewMicrosoft365SignInEvent::class,
        [NewMicrosoft365SignInListener::class, 'handle']
    );
}
```

Setting the routes in `routes/web.php`

Create a login and connect route that loads a AuthController file.

The login route loads a method that in turn loads a view

```php
Route::redirect('/', 'login');

Route::group(['middleware' => ['web', 'guest']], function(){
    Route::get('login', AuthController::class, 'login')->name('login');
    Route::get('connect', AuthController::class, 'connect')->name('connect');
});

Route::group(['middleware' => ['web', 'MsGraphAuthenticated'], 'prefix' => 'app'], function(){
    Route::get('/', PagesController::class, 'app')->name('app');
    Route::get('logout', AuthController::class, 'logout')->name('logout');
});
```

The second group of routes run when a user is connected to MsGraph the middleware `MsGraphAuthenticated` is used to ensure the route won't run unless connected.

Create a controller called AuthController inside `App\Http\Controllers\Auth`.
This has three methods:

Login - loads a view to informing the user to log in with their Microsoft Account
Connect - when called will redirect to the Microsoft Graph login page
Logout - will disconnect from MsGraph and redirect to the desired page.

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Dcblogdev\MsGraph\Facades\MsGraph;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function connect()
    {
        return MsGraph::connect();
    }

    public function logout()
    {
        return MsGraph::disconnect();
    }
}
```

Next, create the view `auth/login.blade.php`, this page uses TailwindCSS for styling but is not required.

This page loads when visiting `/login` and informs the guest to login with their Microsoft account, clicking the login button will redirect to /connect where the Microsoft Graph login page will be loaded.

```php
<div>
    <p>We use Microsoft 365 for accessing your account.</p>
    <p>Click the button below to get started.</p>
</div>
            
<p><a href="{{ route('connect') }}">Login with your Microsoft Account</a></p>
```

Once logged in the normal usage of MsGraph applies, ie to call the users details you can call:

```php
MsGraph::get('me');
```