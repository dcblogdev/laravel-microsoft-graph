---
title: MsGraph
---

A routes example:

When using with existing users then expect a user to be logged in and use auth middleware

```php
Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('msgraph', function(){

        if (! MsGraph::isConnected()) {
            return redirect('msgraph/connect');
        } else {
            //display your details
            return MsGraph::get('me');
        }

    });

    Route::get('msgraph/connect', function(){
        return MsGraph::connect();
    });
});
```

Or using a middleware route, if the user does not have a graph token then automatically redirect to get authenticated:

```php
Route::group(['middleware' => ['web', 'MsGraphAuthenticated']], function(){
    Route::get('msgraph', function(){
        return MsGraph::get('me');
    });
});

Route::get('msgraph/connect', function(){
    return MsGraph::connect();
});
```

Once authenticated you can call MsGraph:: with the following verbs:

```php
MsGraph::get($endpoint, $array = [], $headers, $id = null)
MsGraph::post($endpoint, $array = [], $headers, $id = null)
MsGraph::put($endpoint, $array = [], $headers, $id = null)
MsGraph::patch($endpoint, $array = [], $headers, $id = null)
MsGraph::delete($endpoint, $array = [], $headers, $id = null)
```

The second param of array is not always required, its requirement is determined from the endpoint being called, see the API documentation for more details.

The third param `$headers` is a collection of header options, useful for passing additional header options as required.

$id is optional when used the access token will be attempted to be retrieved based on the id. When omitted the logged-in user will be used.

These expect the API endpoints to be passed, the URL https://graph.microsoft.com/beta/ is provided, only endpoints after this should be used ie:

```php
MsGraph::get('me/messages')
```