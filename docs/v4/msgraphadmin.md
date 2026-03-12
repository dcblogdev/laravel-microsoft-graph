---
title: MsGraph Admin
---

Only administrators can login as tenants.

A routes example:

```php
Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('msgraph', function(){

        if (! MsGraphAdmin::isConnected()) {
            return MsGraphAdmin::connect();
        } else {
            //display all users
            return MsGraphAdmin::get('users');
        }

    });

    Route::get('msgraph/connect', function(){
        return MsGraphAdmin::connect();
    });
});
```

Or using a middleware route, if user does not have a graph token then automatically redirect to get authenticated:

```php
Route::group(['middleware' => ['web', 'MsGraphAdminAuthenticated']], function(){
    Route::get('msgraph', function(){
        //fetch all users
        return MsGraphAdmin::get('users');
    });
});

Route::get('msgraph/connect', function(){
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
MsGraphAdmin::get('users');
```