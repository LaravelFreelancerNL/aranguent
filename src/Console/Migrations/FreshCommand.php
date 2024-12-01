<?php

namespace LaravelFreelancerNL\Aranguent\Console\Migrations;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\FreshCommand as IlluminateFreshCommand;
use Illuminate\Database\Events\DatabaseRefreshed;
use LaravelFreelancerNL\Aranguent\Console\Concerns\ArangoCommands;
use Symfony\Component\Console\Input\InputOption;

class FreshCommand extends IlluminateFreshCommand
{
    use ArangoCommands;

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

        $this->migrator->usingConnection($database, function () use ($database) {
            if ($this->migrator->repositoryExists()) {
                $this->newLine();

                $this->components->task('Dropping all tables', fn() => $this->callSilent('db:wipe', array_filter([
                    '--database' => $database,
                    '--drop-all' => $this->option('drop-all'),
                    '--drop-analyzers' => $this->option('drop-analyzers'),
                    '--drop-graphs' => $this->option('drop-graphs'),
                    '--drop-views' => $this->option('drop-views'),
                    '--drop-types' => $this->option('drop-types'),
                    '--force' => true,
                ])) == 0);
            }
        });

        $this->newLine();

        $this->call('migrate', array_filter([
            '--database' => $database,
            '--path' => $this->input->getOption('path'),
            '--realpath' => $this->input->getOption('realpath'),
            '--schema-path' => $this->input->getOption('schema-path'),
            '--force' => true,
            '--step' => $this->option('step'),
        ]));

        if ($this->laravel->bound(Dispatcher::class)) {
            /** @phpstan-ignore offsetAccess.nonOffsetAccessible   */
            $this->laravel[Dispatcher::class]->dispatch(
                new DatabaseRefreshed($database, $this->needsSeeding()),
            );
        }

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }

        return 0;
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
            ['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed'],
            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
            ['schema-path', null, InputOption::VALUE_OPTIONAL, 'The path to a schema dump file'],
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run'],
            ['seeder', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder'],
            ['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually'],
        ];
    }
}
