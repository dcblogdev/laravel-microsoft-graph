
# Microsoft Graph

A Laravel package for working with Microsoft Graph API.

MsGraph comes in two flavours:

1) MsGraph: login in as a user.
2) MsGraphAdmin: login as a tenant (administrator) useful for running background tasks.

API documentation can be found at https://developer.microsoft.com/en-us/graph/docs/api-reference/beta/beta-overview

## Application Register

To use Microsoft Graph API an application needs creating at [https://apps.dev.microsoft.com](https://apps.dev.microsoft.com) 

Create a new application, name the application. Click continue the Application Id will then be displayed.

Next click Generate New Password under Application Secrets it won't be shown again so ensure you've copied it and added to .env more details further down.

```php
MSGRAPH_CLIENT_ID=
MSGRAPH_SECRET_ID=
```

Now click Add Platform under Platforms and select web.

Enter you desired redirect url. This is the url your application will use to connect to Graph API.

Now under Microsoft Graph Permissions click add and select which permissions to use, a maximum of 20 can be selected.

The other options are optional, click save at the bottom of the page to save your changes.

## Installation 
Via Composer

``` bash
$ composer require daveismyname/laravel-msgraph
```

In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just add the service provider in config/app.php file:

```php
'providers' => [
    // ...
    Daveismyname\MsGraph\MsGraphServiceProvider::class,
];
```

You can publish the migration with:

```bash
php artisan vendor:publish --provider="Daveismyname\MsGraph\MsGraphServiceProvider" --tag="migrations"
```

After the migration has been published you can create the tokens tables by running the migration:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Daveismyname\MsGraph\MsGraphServiceProvider" --tag="config"
```

When published, the config/msgraph.php config file contains:

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

    'urlAuthorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',

    /*
    set the token url
    */
    'urlAccessToken' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',

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

Ensure you've set the following urls in your .env file:

```bash
MSGRAPH_CLIENT_ID=
MSGRAPH_SECRET_ID=

MSGRAPH_OAUTH_URL=https://domain.com/msgraph/oauth
MSGRAPH_LANDING_URL=https://domain.com/msgraph
```

When logging in as a tenant add the tenant ID .env:

```bash
MSGRAPH_TENANT_AUTHORIZE=https://login.microsoftonline.com/{tenant_id}/adminconsent
MSGRAPH_TENANT_TOKEN=https://login.microsoftonline.com/{tenant_id}/oauth2/v2.0/token
```

To find your Office 365 tenant ID in the Azure AD admin center

1) Sign in to the Azure Active Directory admin center [https://aad.portal.azure.com/#blade/Microsoft\_AAD\_IAM/ActiveDirectoryMenuBlade/Overview][2] as a global or user management admin.
2) Under Manage, select Properties. The tenant ID is shown in the Directory ID box.

Optionally add
```bash
MSGRAPH_PREFER_TIMEZONE=
```


## Usage for MsGraph

> Note this package expects a user to be logged in.

A routes example:

```php

Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('msgraph', function(){

        if (! is_string(MsGraph::getAccessToken())) {
            return redirect(env('MSGRAPH_OAUTH_URL'));
        } else {
            //display your details
            return MsGraph::get('me');
        }

    });

    Route::get('msgraph/oauth', function(){
        return MsGraph::connect();
    });
});
```

Or using a middleware route, if user does not have a graph token then automatically redirect to get authenticated

```php
Route::group(['middleware' => ['web', 'MsGraphAuthenticated']], function(){
    Route::get('msgraph', function(){
        return MsGraph::get('me');
    });
});

Route::get('msgraph/oauth', function(){
    return MsGraph::connect();
});
```

Once authenticated you can call MsGraph:: with the following verbs:

```php
MsGraph::get($endpoint, $array = [], $id = null)
MsGraph::post($endpoint, $array = [], $id = null)
MsGraph::put($endpoint, $array = [], $id = null)
MsGraph::patch($endpoint, $array = [], $id = null)
MsGraph::delete($endpoint, $array = [], $id = null)
```

The second param of array is not always required, its requirement is determined from the endpoint being called, see the API documentation for more details.

The third param $id is optional when used the access token will be attempted to be retrieved based on the id. When omitted the logged in user will be used.

These expect the API endpoints to be passed, the url https://graph.microsoft.com/beta/ is provided, only endpoints after this should be used ie:

```php
MsGraph::get('me/messages')
```

To make things a little easier there is also trait classes provided:



### Middleware

To restrict access to routes only to authenticated users there is a middleware route called `MsGraphAuthenticated`

Add `MsGraphAuthenticated` to routes to ensure the user is authenticated:

```php
Route::group(['middleware' => ['web', 'MsGraphAuthenticated'], function()
```

To access token model reference this ORM model:

```php
use Daveismyname\MsGraph\Models\MsGraphToken;
```


## Usage for MsGraphAdmin

> Only administrators can login as tenants.

A routes example:

```php

Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('msgraph', function(){

        if (! is_string(MsGraphAdmin::getAccessToken())) {
            return redirect(env('MSGRAPH_OAUTH_URL'));
        } else {
            //display your details
            return MsGraphAdmin::get('users');
        }

    });

    Route::get('msgraph/oauth', function(){
        return MsGraphAdmin::connect();
    });
});
```

Or using a middleware route, if user does not have a graph token then automatically redirect to get authenticated

```php
Route::group(['middleware' => ['web', 'MsGraphAdminAuthenticated']], function(){
    Route::get('msgraph', function(){
        return MsGraphAdmin::get('users');
    });
});

Route::get('msgraph/oauth', function(){
    return MsGraphAdmin::connect();
});
```

Once authenticated you can call MsGraph:: with the following verbs:

```php
MsGraphAdmin::get($endpoint, $array = [])
MsGraphAdmin::post($endpoint, $array = [])
MsGraphAdmin::put($endpoint, $array = [])
MsGraphAdmin::patch($endpoint, $array = [])
MsGraphAdmin::delete($endpoint, $array = [])
```

The second param is array is not always required, its requirement is determined from the endpoint being called, see the API documentation for more details.

These expect the API endpoints to be passed, the url https://graph.microsoft.com/beta/ is provided, only endpoints after this should be used ie:

```php
MsGraphAdmin::get('users')
```

### Middleware

To restrict access to routes only to authenticated users there is a middleware route called 'MsGraphAuthenticated'

Add `MsGraphAdminAuthenticated` to routes to ensure the user is authenticated:

```php
Route::group(['middleware' => ['web', 'MsGraphAdminAuthenticated'], function()
```

To access token model reference this ORM model:

```php
use Daveismyname\MsGraph\Models\MsGraphToken;
```

## Change log

Please see the [changelog][3] for more information on what has changed recently.

## Contributing

Contributions are welcome and will be fully credited.

Contributions are accepted via Pull Requests on [Github][4].

## Pull Requests

- **Document any change in behaviour** - Make sure the `readme.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0][5]. Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Security

If you discover any security related issues, please email dave@daveismyname.com email instead of using the issue tracker.

## License

license. Please see the [license file][6] for more information.

[2]:    https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/Overview
[3]:    changelog.md
[4]:    https://github.com/daveismyname/laravel-microsoft-graph
[5]:    http://semver.org/
[6]:    license.md