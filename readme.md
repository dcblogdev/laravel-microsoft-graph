
# Microsoft Graph

A Laravel package for working with Microsoft Graph API.

MsGraph comes in two flavours:

1) MsGraph: login in as a user.
2) MsGraphAdmin: login as a tenant (administrator) useful for running background tasks.

API documentation can be found at https://developer.microsoft.com/en-us/graph/docs/api-reference/beta/beta-overview

# Full documentation and install instructions 
[https://docs.dcblog.dev/laravel-microsoft-graph](https://docs.dcblog.dev/laravel-microsoft-graph)

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

If you discover any security related issues, please email dave@dcblog.dev email instead of using the issue tracker.

## License

license. Please see the [license file][6] for more information.

[2]:    https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/Overview
[3]:    changelog.md
[4]:    https://github.com/dcblogdev/laravel-microsoft-graph
[5]:    http://semver.org/
[6]:    license.md