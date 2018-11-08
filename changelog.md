# Changelog

All notable changes to `MsGraph` will be documented in this file.

## Version 1.0.0

### Added
- Everything

## Version 1.1.0
- added 2 traits
	- Emails - methods for listing emails and attachments and sending, replying and forwarding 
	- Contacts - List all contacts
	- fixed migration name and path

## Version 1.1.1
- corrected config publish path

## Version 1.1.2
Added MsGraphAuthenticated to routes to ensure the user is authenticated id:

```
Route::group(['middleware' => ['web', 'MsGraphAuthenticated'], function()
```

Added method getTokenData($id) to return the model object based on the matching user_id from $id
```
public function getTokenData($id = null)
{
    $id = ($id) ? $id : auth()->id();
    return MsGraphToken::where('user_id', $id)->first();
}
```

## Version 1.2.0

Fixed connect method authenticating, now accepts an optional $id defaults to logged in user when not passed directly.

## Version 1.2.1

Added traits:
* Drive
* ToDo

## Version 1.2.2

Updated traits to support correct paging, each trait should return an array containing the total records (where available), top, skip and count keys.
