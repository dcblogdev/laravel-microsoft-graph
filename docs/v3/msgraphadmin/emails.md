---
title: Emails
---

MsGraphAdmin provides a clean way of working with a users emails.

To work with emails first call **->emails()** followed by a method.

Each call needs to use **->userid($userid)** to inform the object which user to work with.

```php
MsGraphAdmin::emails()->userid($userId);
```


## List Emails

Return a list of emails

```php
MsGraphAdmin::emails()->userid($userId)->get();
```

By default, only 10 emails are returned this can be changed by either using GET requests or pass an array of option to get()

Option 1: GET Request

Adding **top=50** to the URL will return 50 emails, to skip the starting point use **skip=2** to start from the 2nd set. These can be used together:

**?top=50&skip=2**

Option 2: Array

The default array that is used internally is below, you can override these options by passing an array to the ->get() method.

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
MsGraphAdmin::emails()->userid($userId)->get([
    "\$orderby" => "displayName",
    "\$top" => 15,
    "\$skip" => 0,
    "\$count" => "true",
]);
```

The response returned is an array in this format:

```php
array:4 [
  "emails" => array:4 [
    "@odata.context" => "https://graph.microsoft.com/beta/$metadata#users('5b7f8791-03a1-4b68-9ff2-5bdca45563')/messages"
    "@odata.count" => 44177
    "@odata.nextLink" => "https://graph.microsoft.com/beta/users/5b7f8791-03a1-4b68-9ff2-5bdca45563/messages?$count=true&$orderby=sentDateTime+desc&$skip=15"
    "value" => [
        "@odata.etag" => "W/"CQAAABYAAAC8b+tAO4nLRZCbkhud5CXFAASVGY/p""
        "id" => "AAMkADdlZTBjNjQ4LWI0OGItNDFhZS05ZDNiLThiY2JkYzIzZWZkYwBGAAAAAABFX7lJCx7ZRLTJ6iI0yZK6BwC8b_tAO4nLRZCbkhud5CXFAAAAAAELAAC8b_tAO4nLRZCbkhud5CXFAASUuapzAAA="
        "createdDateTime" => "2019-05-29T08:58:09Z"
        "lastModifiedDateTime" => "2019-05-29T09:02:00Z"
        "changeKey" => "CQAAABYAAAC8b+tAO4nLRZCbkhud5CXFAASVGY/p"
        "categories" => []
        "receivedDateTime" => "2019-05-29T08:58:09Z"
        "sentDateTime" => "2019-05-29T08:58:04Z"
        "hasAttachments" => false
        "internetMessageId" => ""
        "subject" => "sent you a document to sign"
        "bodyPreview" => You Have Been Sent A Document To Sign
        "importance" => "normal"
        "parentFolderId" => "AQMkADdlZQAwYzY0OC1iNDhiLTQxYWUtOWQzYi04YmNiZGMyM2VmZGMALgAAA0VfuUkLHtlEtMnqIjTJkroBALxv60A7ictFkJuSG53kJcUAAAIBCwAAAA=="
        "conversationId" => "AAQkADdlZTBjNjQ4LWI0OGItNDFhZS05ZDNiLThiY2JkYzIzZWZkYwAQADNDbfE-oxVGsAHIKhk2vCE="
        "conversationIndex" => "AQHVFfyjM0Nt8T+jFUawAcgqGTa8IQ=="
        "isDeliveryReceiptRequested" => null
        "isReadReceiptRequested" => false
        "isRead" => true
        "isDraft" => false
        "webLink" => "https://outlook.office365.com/owa/?ItemID=jQ4LWI0OGItNDFhZS05ZDNiLThiY2JkYzIzZWZkYwBGAAAAAABFX7lJCx7ZRLTJ6iI0yZK6BwC8b%2BtAO4nLRZCbkhud5CXFAAAAAAEL ▶"
        "inferenceClassification" => "other"
        "unsubscribeData" => []
        "unsubscribeEnabled" => false
        "mentionsPreview" => null
        "body" => array:2 [▶]
        "sender" => array:1 [▼
          "emailAddress" => array:2 [▼
            "name" => "via Signable"
            "address" => "document@signable.co.uk"
          ]
        ]
        "from" => array:1 [▼
          "emailAddress" => array:2 [▼
            "name" => "via Signable"
            "address" => "document@signable.co.uk"
          ]
        ]
        "toRecipients" => array:1 [▼
          0 => array:1 [▼
            "emailAddress" => array:2 [▶]
          ]
        ]
        "ccRecipients" => []
        "bccRecipients" => []
        "replyTo" => array:1 [▼
          0 => array:1 [▼
            "emailAddress" => array:2 [▼
              "name" => "John Smith"
              "address" => "j.smith@domain.co.uk"
            ]
          ]
        ]
        "flag" => array:1 [▼
          "flagStatus" => "notFlagged"
        ]
    ]
  "total" => 44177
  "top" => "0"
  "skip" => "15"
]
```

The **@odata.nextLink** is the link for the next set of data that can be used directly or make use of the top and skip that are returned.

## Read Email

To view an email call **->find($id)** followed by the id of the email.

```php
MsGraphAdmin::emails()->userid($userId)->find($id);
```

## Send Email

To send an email the format is different to normal calls. The format is to call multiple methods to set the email properties.

Required methods are: **to**(array) **subject**(string) **body**(string/markup) **send()**

Note these methods expect an array to be passed:

```php
to(['email@domains.com'])
cc(['email@domains.com'])
bcc(['email@domains.com'])
attachments(['path/to/file'])
```

Example:

```php
MsGraphAdmin::emails()
->userid($userId)
->to(['email@domains.com'])
->subject('the subject')
->body('the content')
->send();
```

**cc()** and **bcc** and **attachments** are optional.

To send attachments pass an array of files paths

```php
MsGraphAdmin::emails()
->userid($userId)
->to(['email@domains.com'])
->subject('the subject')
->body('the content')
->attachments([public_path('images/logo')])
->send();
```

## Forward Email

To forward to an email call **->forward()** and use **->comment()** instead of **->body()**.

```php
MsGraphAdmin::emails()
->userid($userId)
->id($id)
->to(['email@domains.com'])
->subject('the subject')
->comment('the reply content')
->forward();
```

## Reply Email

To reply to an email call **->reply()** and use **->comment()** instead of **->body()**.

```php
MsGraphAdmin::emails()
->userid($userId)
->id($id)
->to(['email@domains.com'])
->subject('the subject')
->comment('the reply content')
->reply();
```

## Delete Email

To delete an email call **->delete($id)** followed by the id of the email.

```php
MsGraphAdmin::emails()->userid($userId)->delete($id);
```