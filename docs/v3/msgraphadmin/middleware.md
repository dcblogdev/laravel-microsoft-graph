---
title: Middleware
---

To restrict access to routes only to authenticated users there is a middleware route called **MsGraphAdminAuthenticated**

Add **MsGraphAdminAuthenticated** to routes to ensure the user is authenticated:

```php
Route::group(['middleware' => ['web', 'MsGraphAdminAuthenticated'], function()
```

To access token model reference this ORM model:

```php
use Dcblogdev\MsGraph\Models\MsGraphToken;
```



