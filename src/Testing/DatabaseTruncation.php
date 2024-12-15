<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing;

use Illuminate\Foundation\Testing\DatabaseTruncation as IlluminateDatabaseTruncation;
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
}
