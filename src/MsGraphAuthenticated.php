<?php

namespace Dcblogdev\MsGraph;

use Closure;
use Dcblogdev\MsGraph\Facades\MsGraph;

class MsGraphAuthenticated
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
        if (! MsGraph::isConnected()) {
            return MsGraph::connect();
        }

        return $next($request);
    }
}
