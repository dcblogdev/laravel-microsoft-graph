---
title: Install
---

## Via Composer

```php
composer require dcblogdev/laravel-microsoft-graph
```

## Config
You can publish the config file with:

```php
php artisan vendor:publish --provider="Dcblogdev\MsGraph\MsGraphServiceProvider" --tag="config"
```

When published, the `config/msgraph.php` config file contains:

```php
<?php

return [

    /*
    * the clientId is set from the Microsoft portal to identify the application
    * https://apps.dev.microsoft.com
    */
    'clientId' => env('MSGRAPH_CLIENT_ID'),

    /*
    * set the application secret
    */

    'clientSecret' => env('MSGRAPH_SECRET_ID'),

    /*
    * Set the url to trigger the oauth process this url should call return MsGraph::connect();
    */
    'redirectUri' => env('MSGRAPH_OAUTH_URL'),

    /*
    * set the url to be redirected to once the token has been saved
    */

    'msgraphLandingUri'  => env('MSGRAPH_LANDING_URL'),

    /*
    set the tenant authorize url
    */

    'tenantUrlAuthorize' => env('MSGRAPH_TENANT_AUTHORIZE'),

    /*
    set the tenant token url
    */
    'tenantUrlAccessToken' => env('MSGRAPH_TENANT_TOKEN'),

    /*
    set the authorize url
    */
    'urlAuthorize' => 'https://login.microsoftonline.com/'.env('MSGRAPH_TENANT_ID', 'common').'/oauth2/v2.0/authorize',

    /*
    set the token url
    */
    'urlAccessToken' => 'https://login.microsoftonline.com/'.env('MSGRAPH_TENANT_ID', 'common').'/oauth2/v2.0/token',

    /*
    set the scopes to be used, Microsoft Graph API will accept up to 20 scopes
    */

    'scopes' => 'offline_access openid calendars.readwrite contacts.readwrite files.readwrite mail.readwrite mail.send tasks.readwrite mailboxsettings.readwrite user.readwrite',

    /*
    The default timezone is set to Europe/London this option allows you to set your prefered timetime
    */
    'preferTimezone' => env('MSGRAPH_PREFER_TIMEZONE', 'outlook.timezone="Europe/London"'),
];
```

## Migrations
You can publish the migration with:

```php
php artisan vendor:publish --provider="Dcblogdev\MsGraph\MsGraphServiceProvider" --tag="migrations"
```

## Listeners
Optionally if you plan on using Microsoft Graph as a login system you can publish a listener:

```php
php artisan vendor:publish --provider="Dcblogdev\MsGraph\MsGraphServiceProvider" --tag="Listeners"
```

This contains the following listener:

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

You can customise this to suit your application.

After the migration has been published you can create the tokens tables by running the migration:

```php
php artisan migrate
```

.ENV Configuration
Ensure you've set the following in your .env file:

```php
MSGRAPH_CLIENT_ID=
MSGRAPH_SECRET_ID=

MSGRAPH_OAUTH_URL=https://domain.com/msgraph/oauth
MSGRAPH_LANDING_URL=https://domain.com/msgraph
```

If you've setup a single-tenant application make sure to include the tenant ID in the .env:

The tenantID value can be seen in the application you've created at https://portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps click on your application, the Directory (tenant) ID will be listed at the top of the page.

Adding the tenant_id changed some of the URLs from using /common/ to using the supplied tenant ID

```php
MSGRAPH_TENANT_ID=
```

When logging in as a tenant (for Admin access) add the tenant ID .env:

```php
MSGRAPH_TENANT_AUTHORIZE=https://login.microsoftonline.com/{tenant_id}/adminconsent
MSGRAPH_TENANT_TOKEN=https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/token
```

Optionally add

```php
MSGRAPH_PREFER_TIMEZONE='outlook.timezone="Europe/London"'
```

To set a prefered database connection add

```php 
MSGRAPH_DB_CONNECTION=sqlite
```

## Application Register

To use Microsoft Graph API an application needs creating.

Sign in to the https://portal.azure.com/ using either a work or school account or a personal Microsoft account.

If your account gives you access to more than one tenant, select your account in the top right corner, and set your portal session to the Azure AD tenant that you want.

In the left-hand navigation pane, select the Azure Active Directory service, and then select `App registrations > New registration`.

When the Register an application page appears, enter your application's registration information:

Name - Enter a meaningful application name that will be displayed to users of the app.
Supported account types - Select which accounts you would like your application to support.
Enter you desired redirect url. This is the url your application will use to connect to Graph API.

Next click Register on the next page take a note of the Application (client) ID.

Add the following to your .env file, change the domain to match your own.

```php
MSGRAPH_CLIENT_ID=
MSGRAPH_SECRET_ID=
MSGRAPH_TENANT_ID=
MSGRAPH_OAUTH_URL=http://domain.com/msgraph/oauth
MSGRAPH_LANDING_URL=http://domain.com/msgraph
```

Add the client id to the .env file.

Next click Certificate & Secrets and click new client secret

Enter a description and expiration option. Copy secret to .env

Now go to API Permissions. click add permission.

First, select the group type followed by the permission. For instance, when working with emails select the exchange group.