<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing;

use Illuminate\Foundation\Testing\DatabaseMigrations as IlluminateDatabaseMigrations;

trait DatabaseMigrations
{
    use IlluminateDatabaseMigrations;

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
