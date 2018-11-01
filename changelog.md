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
Add MsGraphAuthenticated to routes to ensure the user is authenticated id:

```
Route::group(['middleware' => ['web', 'MsGraphAuthenticated'], function()
```
