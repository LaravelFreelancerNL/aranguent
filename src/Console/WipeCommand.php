<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Console\WipeCommand as IlluminateWipeCommand;
use Symfony\Component\Console\Input\InputOption;

class WipeCommand extends IlluminateWipeCommand
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables, views, views, custom analyzers, named graphs and types';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->isProhibited() ||
            ! $this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $database = $this->input->getOption('database');

        $this->handleDrops($database);

        return 0;
    }

    /**
     * @param string $database
     * @return void
     */
    protected function handleDrops($database)
    {
        if ($this->option('drop-graphs') || $this->option('drop-all')) {
            $this->dropAllGraphs($database);

            $this->components->info('Dropped all named graphs successfully.');
        }

        if ($this->option('drop-views') || $this->option('drop-all')) {
            $this->dropAllViews($database);

            $this->components->info('Dropped all views successfully.');
        }

        $this->dropAllTables($database);

        $this->components->info('Dropped all tables successfully.');

        if ($this->option('drop-types')) {
            $this->dropAllTypes($database);

            $this->components->info('Dropped all types successfully.');
        }

        if ($this->option('drop-analyzers') || $this->option('drop-all')) {
            $this->dropAllAnalyzers($database);

            $this->components->info('Dropped all analyzers successfully.');
        }
    }

    /**
     * Drop all of the database analyzers.
     *
     * @param  null|string  $database
     * @return void
     */
    protected function dropAllAnalyzers($database)
    {
        /** @phpstan-ignore offsetAccess.nonOffsetAccessible   */
        $this->laravel['db']->connection($database)
            ->getSchemaBuilder()
            ->dropAllAnalyzers();
    }

    /**
     * Drop all of the database analyzers.
     *
     * @param  null|string  $database
     * @return void
     */
    protected function dropAllGraphs($database)
    {
        /** @phpstan-ignore offsetAccess.nonOffsetAccessible   */
        $this->laravel['db']->connection($database)
            ->getSchemaBuilder()
            ->dropAllGraphs();
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array<int, int|string|null>>
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],
            ['drop-all', null, InputOption::VALUE_NONE, 'Drop all tables, views, custom analyzers and named graphs (ArangoDB only)'],
            ['drop-analyzers', null, InputOption::VALUE_NONE, 'Drop all tables and custom analyzers (ArangoDB only)'],
            ['drop-graphs', null, InputOption::VALUE_NONE, 'Drop all tables and named graphs (ArangoDB only)'],
            ['drop-views', null, InputOption::VALUE_NONE, 'Drop all tables and views (ArangoDB only)'],
            ['drop-types', null, InputOption::VALUE_NONE, 'Drop all tables and types (Postgres only)'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }
}
