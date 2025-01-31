<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing;

use Illuminate\Foundation\Testing\DatabaseTransactionsManager;
use Illuminate\Foundation\Testing\RefreshDatabase as IlluminateRefreshDatabase;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\CanConfigureMigrationCommands;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\PreparesTestingTransactions;

trait RefreshDatabase
{
    use PreparesTestingTransactions;
    use CanConfigureMigrationCommands;
    use IlluminateRefreshDatabase;

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginDatabaseTransaction()
    {
        $database = $this->app->make('db');

        $connections = $this->connectionsToTransact();

        $this->app->instance('db.transactions', $transactionsManager = new DatabaseTransactionsManager($connections));

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);

            $connection->setTransactionManager($transactionsManager);

            $dispatcher = $connection->getEventDispatcher();

            $connection->unsetEventDispatcher();
            $connection->beginTransaction($this->transactionCollections);
            $connection->setEventDispatcher($dispatcher);
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = $database->connection($name);
                $dispatcher = $connection->getEventDispatcher();

                $connection->unsetEventDispatcher();
                $connection->rollBack();
                $connection->setEventDispatcher($dispatcher);
                $connection->disconnect();
            }
        });
    }


    /**
     * The parameters that should be used when running "migrate:fresh".
     *
     * Duplicate code because CanConfigureMigrationCommands has a conflict otherwise.
     *
     * @return array
     */
    protected function migrateFreshUsing()
    {
        $seeder = $this->seeder();

        $results =  array_merge(
            [
                '--drop-analyzers' => $this->shouldDropAnalyzers(),
                '--drop-graphs' => $this->shouldDropGraphs(),
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
                '--drop-all' => $this->shouldDropAll(),
            ],
            $seeder ? ['--seeder' => $seeder] : ['--seed' => $this->shouldSeed()],
            $this->setMigrationPaths(),
        );

        return $results;
    }

    /**
     * Determine if types should be dropped when refreshing the database.
     *
     * @return array<string, array<string>|string>
     */
    protected function setMigrationPaths()
    {
        $migrationSettings = [];

        if (property_exists($this, 'realPath')) {
            $migrationSettings['--realpath'] = $this->realPath ?? false;
        }

        if (property_exists($this, 'migrationPaths')) {
            $migrationSettings['--path'] = $this->migrationPaths;
        }

        return $migrationSettings;
    }
}
