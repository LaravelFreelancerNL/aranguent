<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing;

use Illuminate\Foundation\Testing\DatabaseTransactionsManager;
use Illuminate\Foundation\Testing\RefreshDatabase as IlluminateRefreshDatabase;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\PreparesTestingTransactions;

trait RefreshDatabase
{
    use PreparesTestingTransactions;
    use IlluminateRefreshDatabase;

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginDatabaseTransaction()
    {
        $database = $this->app->make('db');

        $this->app->instance('db.transactions', $transactionsManager = new DatabaseTransactionsManager());

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
     * @return array
     */
    protected function migrateFreshUsing()
    {
        $seeder = $this->seeder();

        $results =  array_merge(
            [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
            ],
            $seeder ? ['--seeder' => $seeder] : ['--seed' => $this->shouldSeed()],
            $this->setMigrationPaths(),
        );

        ray('migrateFreshUsing', $results);
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

        if(property_exists($this, 'realPath')) {
            $migrationSettings['--realpath'] = $this->realPath ?? false;
        }

        if (property_exists($this, 'migrationPaths')) {
            $migrationSettings['--path'] = $this->migrationPaths;
        }

        return $migrationSettings;
    }
}
