---
title: Queues
---

If you want to use `MsGraph` within a job or command, you can use the `login` method to authenticate the user. Make sure to pass the user model to the `login` method. The User needs to have the `access_token` and `refresh_token` stored in the database.

```php
MsGraph::login(User:find(1));

MsGraph->get('me');
```

Example Job:

```php
<?php

namespace App\Jobs;

use App\Models\User;
// ...

class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        MsGraph::login($this->user);

        $me = MsGraph::get("me");
    }
}
```