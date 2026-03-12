---
title: Is Connected
---

Anytime you need to check if MsGraph is authenticated you can call a **->isConnected** method. The method returns a boolean.

To do an action when a MsGraph is not connected can be done like this:

```php
if (! MsGraph::isConnected()) {
    return redirect('msgraph/connect');
}
```


