<?php

namespace Dcblogdev\MsGraph\Tests;

use Dcblogdev\MsGraph\MsGraphServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            MsGraphServiceProvider::class,
        ];
    }
}
