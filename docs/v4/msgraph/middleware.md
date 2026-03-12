---
title: Middleware
---

To restrict access to routes only to authenticated users there is a middleware route called **MsGraphAuthenticated**

Add **MsGraphAuthenticated** to routes to ensure the user is authenticated:

```php
Route::group(['middleware' => ['web', 'MsGraphAuthenticated'], function()
```

To access token model reference this ORM model:

```php
use Dcblogdev\MsGraph\Models\MsGraphToken;
```