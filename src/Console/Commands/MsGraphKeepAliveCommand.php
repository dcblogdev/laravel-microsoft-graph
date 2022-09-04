<?php

namespace Dcblogdev\MsGraph\Console\Commands;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Console\Command;

class MsGraphKeepAliveCommand extends Command
{
    protected $signature   = 'msgraph:keep-alive';
    protected $description = 'Run this command to refresh token if its due to expire. schedule this to run daily to avoid token expiring when using CLI commands';

    public function handle()
    {
        if (MsGraph::isConnected()) {
            MsGraph::getAccessToken($returnNullNoAccessToken = true);
            $this->comment('connected');
        }
    }
}