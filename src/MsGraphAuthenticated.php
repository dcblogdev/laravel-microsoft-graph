<?php

namespace DaveismynameLaravel\MsGraph;

use Closure;
use DaveismynameLaravel\MsGraph\Facades\MsGraph;

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
