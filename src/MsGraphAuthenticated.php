<?php

namespace Daveismyname\MsGraph;

use Closure;
use Daveismyname\MsGraph\Facades\MsGraph;

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
        if (MsGraph::getTokenData() === null) {
            return MsGraph::connect();
        }

        return $next($request);
    }
}
