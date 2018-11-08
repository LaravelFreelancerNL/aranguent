<?php

namespace LaravelFreelancerNL\Aranguent\Tests;

use Illuminate\Support\Facades\DB;
use LaravelFreelancerNL\Aranguent\AranguentServiceProvider;
use LaravelFreelancerNL\Aranguent\Migrations\DatabaseMigrationRepository;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    protected $collection = 'migrations';

    protected $connection;

    protected $collectionHandler;

    protected $databaseMigrationRepository;


    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
//        $this->withFactories(__DIR__ . '/database/factories');

        $config = require 'config/database.php';

        $app['config']->set('database.default', 'arangodb');
        $app['config']->set('database.connections.arangodb', $config['connections']['arangodb']);
        $app['config']->set('database.connections.mysql', $config['connections']['mysql']);
        $app['config']->set('database.connections.sqlite', $config['connections']['sqlite']);

        $app['config']->set('cache.driver', 'array');

        $this->connection = DB::connection('arangodb');

        $this->collectionHandler = $this->connection->getCollectionHandler();

        //Remove all collections
        $collections = $this->collectionHandler->getAllCollections(['excludeSystem' => true]);
        foreach ($collections as $collection) {
            $this->collectionHandler->drop($collection['id']);
        }


    }

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate:install', [])->run();

        $this->databaseMigrationRepository = new DatabaseMigrationRepository($this->app['db'], $this->collection);

    }

    protected function getPackageProviders($app)
    {
        return [
            AranguentServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Aranguent' => 'LaravelFreelancerNL\Aranguent'
        ];
    }

}
