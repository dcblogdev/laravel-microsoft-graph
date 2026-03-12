---
title: Files
---

MsGraph provides a clean way of working with files. The default setup is to use the authenticated user's OneDrive.

You can use Sharepoint or Group files instead by passing an additional argument to all **files()** calls with the prefix which defaults to 'me' 

Example get all files from OneDrive:

```php
MsGraph::files()->getFiles($path = null, $type = 'me');
```

Get files from a group:

```php
MsGraph::files()->getFiles($path = null, $type = "groups/$groupId");
```

To work with files first call **->files()** followed by a method.

```php
MsGraph::files();
```

Methods:

## List files and folders

```php
MsGraph::files()->getFiles($path = null, $type = 'me');
```

## List drive

```php
MsGraph::files()->getDrive();
```

## List drives

```php
MsGraph::files()->getDrives();
```

## Search items

```php
MsGraph::files()->search($term);
```

## Download file by id

```php
MsGraph::files()->downloadFile($id);
```

## Delete file by id

```php
MsGraph::files()->deleteFile($id);
```

## Create folder

Pass the folder and the path where the folder will be created if no path is provided the root is used.

```php
MsGraph::files()->createFolder($name, $path = null);
```

## Get file/folder item by id

```php
MsGraph::files()->getItem($id);
```

## Rename file/folder

pass the new name and the id 

```php
MsGraph::files()->rename($name, $id);
```

## Upload file

Pass the name and the uploadPath (where the file is on your server) and the path to where the file will be stored. If no path is provided the root is used.

```php
MsGraph::files()->upload($name, $uploadPath, $path = null);
```
