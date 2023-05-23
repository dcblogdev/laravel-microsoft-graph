<?php

namespace Dcblogdev\MsGraph;

use Closure;
use Dcblogdev\MsGraph\Facades\MsGraphAdmin;

class MsGraphAdminAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! MsGraphAdmin::isConnected()) {
            return MsGraphAdmin::connect();
        }

        return $next($request);
    }
}
