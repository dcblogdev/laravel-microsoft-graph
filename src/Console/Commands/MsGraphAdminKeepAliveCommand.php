<?php

namespace Dcblogdev\MsGraph\Console\Commands;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Illuminate\Console\Command;

class MsGraphAdminKeepAliveCommand extends Command
{
    protected $signature   = 'msgraphadmin:keep-alive';
    protected $description = 'Run this command to refresh token if its due to expire. schedule this to run daily to avoid token expiring when using CLI commands';

    public function handle()
    {
        if (MsGraphAdmin::isConnected()) {
            MsGraphAdmin::getAccessToken($returnNullNoAccessToken = true);
            $this->comment('connected');
        }
    }
}