<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\DatabaseTruncation as IlluminateDatabaseTruncation;
use Illuminate\Support\Collection;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\CanConfigureMigrationCommands;

trait DatabaseTruncation
{
    use IlluminateDatabaseTruncation;
    use CanConfigureMigrationCommands;

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
     * Determine if a table exists in the given list, with or without its schema.
     */
    protected function tableExistsIn(array $table, array $tables): bool
    {
        return isset($table['schema'])
            ? ! empty(array_intersect([$table['name'], $table['schema'] . '.' . $table['name']], $tables))
            : in_array($table['name'], $tables);
    }

    /**
     * Truncate the database tables for the given database connection.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  string|null  $name
     * @return void
     */
    protected function truncateTablesForConnection(ConnectionInterface $connection, ?string $name): void
    {
        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();

        (new Collection($this->getAllTablesForConnection($connection, $name)))
            ->when(
                $this->tablesToTruncate($connection, $name),
                function (Collection $tables, array $tablesToTruncate) {
                    return $tables->filter(fn(array $table) => $this->tableExistsIn($table, $tablesToTruncate));
                },
                function (Collection $tables) use ($connection, $name) {
                    $exceptTables = $this->exceptTables($connection, $name);
                    return $tables->filter(fn(array $table) => ! $this->tableExistsIn($table, $exceptTables));
                },
            )
            ->each(function (array $table) use ($connection) {
                $connection->withoutTablePrefix(function ($connection) use ($table) {
                    $table = $connection->table(
                        isset($table['schema']) ? $table['schema'] . '.' . $table['name'] : $table['name'],
                    );
                    if ($table->exists()) {
                        $table->truncate();
                    }
                });
            });

        $connection->setEventDispatcher($dispatcher);
    }

}
