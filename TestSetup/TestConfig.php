<?php

namespace TestSetup;

use Illuminate\Config\Repository;

class TestConfig
{
    public static function set($app)
    {
        tap($app->make('config'), function (Repository $config) {
            $config->set('database.connections.arangodb', [
                'name'                                      => 'arangodb',
                'driver'                                    => 'arangodb',
                'endpoint'                                  => env('DB_ENDPOINT', 'http://localhost:8529'),
                'username'                                  => env('DB_USERNAME', 'root'),
                'password'                                  => env('DB_PASSWORD', null),
                'database'                                  => env('DB_DATABASE', 'aranguent__test'),
            ]);
            $config->set('database.default', 'arangodb');
        });
    }
}
