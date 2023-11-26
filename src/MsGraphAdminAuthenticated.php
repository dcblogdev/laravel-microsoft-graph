<?php

namespace Dcblogdev\MsGraph;

use Closure;
use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Illuminate\Http\Request;

class MsGraphAdminAuthenticated
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! MsGraphAdmin::isConnected()) {
            return MsGraphAdmin::connect();
        }

        return $next($request);
    }
}
