<?php

namespace Dcblogdev\MsGraph\Tests;

use create_ms_graph_tokens_table;
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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        require_once 'src/database/migrations/create_ms_graph_tokens_table.php';

        // run the up() method of that migration class
        (new create_ms_graph_tokens_table)->up();
    }
}
