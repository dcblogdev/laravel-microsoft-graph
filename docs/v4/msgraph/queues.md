---
title: Queues
---

When using `MsGraph` within jobs, commands, or other queued processes, you need to authenticate the user explicitly. Ensure the user model has `access_token` and `refresh_token` stored in the database. Use the `login` method to authenticate the user before making any API calls:

```php
MsGraph::login(User::find(1));
MsGraph::get('me');
```

Here's an example of how to structure a job that uses `MsGraph`:

```php
<?php

namespace App\Jobs;

use App\Models\User;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExampleMsGraphJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        MsGraph::login($this->user);
        $userData = MsGraph::get('me');
        // Process $userData as needed
    }
}
```

Dispatch this job with a user instance:

```php
ExampleMsGraphJob::dispatch($user);
```

This approach ensures that the Microsoft Graph API calls are made with the correct user context, even in background processes.