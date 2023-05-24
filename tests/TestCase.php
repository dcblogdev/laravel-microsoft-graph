<?php

namespace Dcblogdev\MsGraph\Tests;

use Dcblogdev\MsGraph\MsGraphServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use function Orchestra\Testbench\artisan;

class TestCase extends Orchestra
{
    //use DatabaseMigrations;

    protected function getPackageProviders($app)
    {
        return [
            MsGraphServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'sqlite',
            'host'   => '127.0.0.1',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');

//        artisan($this, 'migrate');
//
//        $this->beforeApplicationDestroyed(
//            fn () => artisan($this, 'migrate:rollback')
//        );
    }
}

