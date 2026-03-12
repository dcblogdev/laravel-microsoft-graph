---
title: Contacts
---

MsGraph provides a clean way of working with a user's contacts.

To work with contacts first call **->contacts()** followed by a method.

```php
MsGraph::contacts()
```

## Listing Contacts

Return a list of all contacts

```php
MsGraph::contacts()->get()
```

By default, only 10 contacts are returned this can be changed by either using GET requests or pass an array of option to get()

Option 1: GET Request

Adding **top=50** to the URL will return 50 contacts, to skip the starting point use **skip=2** to start from the 2nd set. These can be used together:

```php
?top=50&skip=2
```

Option 2: Array

The default array that is used internally is below, you can override these options by passing an array to the **->get()** method.

```php
[
    "\$orderby" => "displayName",
    "\$top" => $top,
    "\$skip" => $skip,
    "\$count" => "true",
]
```

This would look like this:

```php
MsGraph::contacts()->get([
    "\$orderby" => "displayName",
    "\$top" => 100,
    "\$skip" => 0,
    "\$count" => "true",
]);
```

The response returned is an array in this format:

```php
array:4 [
  "contacts" => array:4 [
    "@odata.context" => "https://graph.microsoft.com/beta/$metadata#users('5b7f8791-03a1-4b68-9ff2-5bdca45563')/contacts"
    "@odata.count" => 1112
    "@odata.nextLink" => "https://graph.microsoft.com/beta/users/5b7f8791-03a1-4b68-9ff2-5bdca45563/contacts?$orderby=displayName&$top=50&$skip=52&$count=true"
    "value" => array:50 [
        0 => array:38 [
        "@odata.etag" => "W/"BYAAAALc1XGBA2GTZfCUzLE1FjyAAKvR6Gl""
        "id" => "hhOTJlODA2LTUwYTQtNGNmMS1hZWQ2LWVjZjU1OTZiYzY4OQBGAAAAAAAbbv6dt9gvS71-sGvg9qUVBwALc1XGBA2GTZfCUzLE1FjyAAAAui-HAAALc1XGBA2GTZfCUzLE1FjyAAEEO4cvAAA="
        "createdDateTime" => "2017-06-15T21:47:53Z"
        "lastModifiedDateTime" => "2019-04-04T21:26:50Z"
        "changeKey" => "EQAAABYAAAALc1XGBA2GTZfCUzLE1FjyAAKvR6Gl"
        "categories" => []
        "parentFolderId" => "TJlODA2LTUwYTQtNGNmMS1hZWQ2LWVjZjU1OTZiYzY4OQAuAAAAAAAbbv6dt9gvS71-sGvg9qUVAQALc1XGBA2GTZfCUzLE1FjyAAAAui-HAAA="
        "birthday" => null
        "fileAs" => ""
        "displayName" => "​John Smith"
        "givenName" => null
        "initials" => null
        "middleName" => null
        "nickName" => null
        "surname" => null
        "title" => null
        "yomiGivenName" => null
        "yomiSurname" => null
        "yomiCompanyName" => null
        "generation" => null
        "imAddresses" => []
        "jobTitle" => null
        "companyName" => null
        "department" => null
        "officeLocation" => null
        "profession" => null
        "assistantName" => null
        "manager" => null
        "spouseName" => null
        "personalNotes" => ""
        "children" => []
        "gender" => null
        "isFavorite" => null
        "emailAddresses" => array:1 [
             0 => array:3 [
                "type" => "unknown"
                "name" => "John Smith"
                "address" => "j.smith@domain.co.uk"
              ]
            ]
        ]
        "websites" => []
        "phones" => []
        "postalAddresses" => []
        "flag" => array:1 [
          "flagStatus" => "notFlagged"
        ]
      ]
    ]
  ]
  "total" => 1112
  "top" => "50"
  "skip" => "52"
]
```

The **@odata.nextLink** is the link for the next set of data that can be used directly or make use of the top and skip that are returned.

## Create Contacts

To create a contact call **->store($data)** and pass in an array of details for the contact.

To see all contact properties look at https://docs.microsoft.com/en-us/graph/api/resources/contact?view=graph-rest-1.0

```php
$data = [
    'displayName' => 'John Smith',
    'givenName' => 'John Smith',
    'emailAddresses' => [
        [
            'address' => 'j.smith@domain.com',
            'name' => 'John Smith'
        ]
    ]
];

MsGraph::contacts()->store($data);
```

## Edit Contacts

To update a contact call **->update($data)** and pass in the id of the contact and an array of details to be changed.

To see all contact properties look at https://docs.microsoft.com/en-us/graph/api/resources/contact?view=graph-rest-1.0

In this example, only the email address will be changed

```php
$data = [
    'emailAddresses' => [
        [
            'address' => 'j.agent@domain.com',
            'name' => 'John Agent'
        ]
    ]
];

MsGraph::contacts()->update($id, $data);
```

## View Contacts

To view a contact call **->find($id)** followed by the id of the contact.

```php
MsGraph::contacts()->find($id);
```

## Delete Contacts

To delete a contact call **->delete($id)** followed by the id of the contact.

```php
MsGraph::contacts()->delete($id);
```






