---
title: Add Sharepoint/OneDrive file storage
---

# Add Sharepoint/OneDrive file storage

This package make use of [shitware-ltd/flysystem-msgraph](https://github.com/shitware-ltd/flysystem-msgraph) internally 

>  A flysystem 3.0 adapter for Sharepoint 365 / OneDrive using Microsoft Graph API with support for uploading large files

Since **flysystem-msgraph** package uses the official **Microsoft Graph SDK** you can use Microsoft Graph directly by calling 

```php
$graph = new \Microsoft\Graph\Graph;
```

## Set up

In **filesystems.php** add

```php
'msgraph' => [
    'driver' => 'msgraph',
    'driveId' => env('MSGRAPH_DRIVEID', ''),
],
```

Inside `.env` add a key:

Add the drive id to be used.

```php
MSGRAPH_DRIVEID=''
```

To find your drives use the Graph Explorer [https://developer.microsoft.com/en-us/graph/graph-explorer](https://developer.microsoft.com/en-us/graph/graph-explorer)

Login and then enter the url https://graph.microsoft.com/v1.0/drives

Inside the returned payload you’re looking for an id property that looks like this:

>  "id": "b!zMM792FbNkq...",


## Usage 

using the msgraph driver is a case of specifying the disk `Storage::disk('msgraph')`

A few examples:

```php
//make folder
Storage::disk('msgraph')->makeDirectory('assets');

//upload file
Storage::disk('msgraph')->put('demo.txt', 'hello');

//move demo.txt from a test folder to the root
Storage::disk('msgraph')->move('test/demo.txt', 'demo.txt');

//copy demo.txt from the root to a test folder
Storage::disk('msgraph')->copy('demo.txt', 'test/demo.txt');

//download file
Storage::disk('msgraph')->download('demo2.txt');

//get a list of files from the root
Storage::disk('msgraph')->files();

//get a list of folders from the root
Storage::disk('msgraph')->directories();
```

See [https://laravel.com/docs/10.x/filesystem](https://laravel.com/docs/10.x/filesystem) for more details on using Laravel file storage.