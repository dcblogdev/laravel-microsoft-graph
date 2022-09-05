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

## Version 1.1.3

Fixed connect method authenticating, now accepts an optional $id defaults to logged in user when not passed directly.

Added traits:
* Drive
* ToDo

## Version 1.1.4

Updated traits to support correct paging, each trait should return an array containing the total records (where available), top, skip and count keys.

Added new traits:
* Calendar
* CalendarEvents
* Events

Renamed all methods to be action followed by name ie `getEmails`

## Version 1.1.5

Renamed repo to daveismyname/laravel-msgraph

## Version 1.2.0

Fixed install error

Corrected path in composer.json stopping installation.

## Version 1.3.0

Added ability to login as a tenant by using MsGraphAdmin

## Version 2.0.0

Renamed repo to daveismyname/laravel-microsoft-graph
Added tenency support
Removed traits and added classes in resources instead

## Version 3.0.0

Renamed repo to dcblogdev/laravel-microsoft-graph

## Version 3.0.1

Added support for Laravel 7

## Version 3.0.2

Calling the API with the id

When calling the connect-method with explicit id it would fail trying to retrieve the users email address. This issue is fixed by calling the API with the id.

## Version 3.0.3

added support for Laravel 8

## Version 3.0.4

Laravel 8 and Guzzle 7 support

Guzzle has been upgraded from version 6 to 7 and Laravel 8 (illuminate/support) has been added.

Base url has also changed from the /beta endpoing to 1.0

https://graph.microsoft.com/v1.0/

## Version 3.0.5

Support for Guzzle 6 and 7

Added support for both Guzzle 6 and 7 since older versions of Laravel required Guzzle 6.

## Version 3.0.6

patch for guzzle 6/7

## Version 3.0.7

supports login ability

Added new methods: isConnected() and disconnect()
fires an event when a user logs in
config uses tenant id for authorise urls when set in .env
added a publishing option for listeners
added an event NewMicrosoft365SignInEvent that fires on login.

## Version 3.0.8

Fix issue when connecting with specified ID

Merge pull request #14 from stromgren/explicit-id

Fix issue when connecting with specified ID

## Version 3.0.9

added file methods

Added methods:

List files and folders

```php
MsGraph::files()->getFiles($path = null, $order = 'asc');
```

List drive

```php
MsGraph::files()->getDrive();
```

List drives

```php
MsGraph::files()->getDrives();
```

Search items

```php
MsGraph::files()->search($term);
```

Download file by id

```php
MsGraph::files()->downloadFile($id)
```

Delete file by id

```php
MsGraph::files()->deleteFile($id)
```

Create folder pass the folder and the path where the folder will be created if no path is provided the root is used.

```php
MsGraph::files()->createFolder($name, $path = null)
```

Get file/folder item by id

```php
MsGraph::files()->getItem($id)
```

Rename file/folder pass the new name and the id

```php
MsGraph::files()->rename($name, $id)
```

Upload file passes the name and the uploadPath (where the file is on your server) and the path to where the file will be stored if no path is provided the root is used.

```php
MsGraph::files()->upload($name, $uploadPath, $path = null)
```

## Version 3.0.10

Changed files to support passing the prefix to the paths such as me or groups/$groupId or sites.

Example

```php
//set a custom prefix to a set group
MsGraph::files()->getFiles($this->path, "groups/$groupId");

//use the default (me)
MsGraph::files()->getFiles($this->path);
```

## Version 3.0.11

Added classes for MsGraphAdmin for working with Calendars and Events

Calendar Events
```php 
MsGraphAdmin::calendarEvents()->userid($userId)->get();
MsGraphAdmin::calendarEvents()->userid($userId)->find($calendarId, $eventId);
MsGraphAdmin::calendarEvents()->userid($userId)->store($calendarId, $data);
```

Calendars
```php 
MsGraphAdmin::calendars()->userid($userId)->get();
MsGraphAdmin::calendars()->userid($userId)->find($eventId);
MsGraphAdmin::calendars()->userid($userId)->store($data);
MsGraphAdmin::calendars()->userid($userId)->update($data);
```

Events
```php 
MsGraphAdmin::events()->userid($userId)->get();
MsGraphAdmin::events()->userid($userId)->find($eventId);
MsGraphAdmin::events()->userid($userId)->store($data);
MsGraphAdmin::events()->userid($userId)->update($data);
MsGraphAdmin::events()->userid($userId)->delete($data);
```

### 3.1.0

Changed getPagination() to return array containing only previous and next page numbers.

This method needs the data but also the total number of records, the limit ($top) and the offset ($skip)

```php 
$limit = 5;
$skip  = request('next', 0);

$messageQueryParams = [
	"\$orderby" => "displayName",
	"\$count"   => "true",
	"\$skip"    => $skip,
	"\$top"     => $limit,
];

$contacts = MsGraph::get('me/contacts?'.http_build_query($messageQueryParams));
$total    = $contacts['@odata.count'] ?? 0;

$response = MsGraph::getPagination($contacts, $total, $limit, $skip);
$previous = $response['previous'];
$next     = $response['next'];
```

The in a view the previous and next links can be displayed:

```php 
@if (request('next') > 0)
	<a href='{{ url()->current().'?next='.$previous }}'>Previous Page</a>
@endif

@if ($next != 0)
	<a href='{{ url()->current().'?next='.$next }}'>Next Page</a>
@endif
```

### 3.1.2

added support for Laravel 9

### 3.1.3

Added files import for MsGraphAdmin 

### 3.1.4

## Added

- Test foundation using PestPHP
- PHP code sniffer fixer and style config

## Changed

- `MSGRAPH_DB_CONNECTION` to be mysql to use a connection called mysql
- Store name is email cannot be found when connecting
- Changed responses so if the data is json it gets decoded otherwise the raw body is returned
- `Msgraph::emails->get($folderId, $params)` returns error when mailbox folder not found

## Fixed

- used MsGraphAdmin instead of MsGraph in admin files resource

### 3.1.5

## Added

- added commands `msgraphadmin:keep-alive` and `msgraph:keep-alive` to allow refresh tokens to be automated by running these commands on a schedule
- added support in Files.php to support replace/rename behavior on `createFolder` and file `upload` functions. Default behavior is to rename.

Usage for createFolder:
```php
MsGraph::files()->createFolder($name, $path, $type = 'me', $behavior='rename')
```
Where $behavior is either rename or replace

Usage for upload:
```php
MsGraph::files()->upload($name, $uploadPath, $path=null, $type='me',$behavior='rename')
```
Where $behavior is either rename or replace
