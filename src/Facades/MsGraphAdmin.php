<?php

namespace Dcblogdev\MsGraph\Facades;

use Illuminate\Support\Facades\Facade;

class MsGraphAdmin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'msgraphadmin';
    }
}
