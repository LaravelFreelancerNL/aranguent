<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing\Concerns;

trait CanConfigureMigrationCommands
{
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

    /**
     * Determine if custom analyzers should be dropped when refreshing the database.
     *
     * @return bool
     */
    protected function shouldDropAnalyzers()
    {
        return property_exists($this, 'dropAnalyzers') ? $this->dropAnalyzers : false;
    }

    /**
     * Determine if graphs should be dropped when refreshing the database.
     *
     * @return bool
     */
    protected function shouldDropGraphs()
    {
        return property_exists($this, 'dropGraphs') ? $this->dropGraphs : false;
    }


    /**
     * Determine if all analyzers, graphs and views should be dropped when refreshing the database.
     *
     * @return bool
     */
    protected function shouldDropAll()
    {
        return property_exists($this, 'dropAll') ? $this->dropAll : false;
    }
}
