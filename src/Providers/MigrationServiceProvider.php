<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Providers;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\MigrationServiceProvider as IlluminateMigrationServiceProvider;
use LaravelFreelancerNL\Aranguent\Console\Concerns\CommandNameSpace;
use LaravelFreelancerNL\Aranguent\Console\Migrations\MigrationsConvertCommand;
use LaravelFreelancerNL\Aranguent\Console\Migrations\MigrateMakeCommand;
use LaravelFreelancerNL\Aranguent\Migrations\DatabaseMigrationRepository;
use LaravelFreelancerNL\Aranguent\Migrations\MigrationCreator;

class MigrationServiceProvider extends IlluminateMigrationServiceProvider
{
    use CommandNameSpace;

    protected bool $defer = true;

    protected $aliases = [
        'Migrator' => 'migrator',
        'Creator' => 'migration.creator',
        'Repository' => 'migration.repository',
        'MigrateMake' => 'migrate.make',
//        'Migrate' => MigrateCommand::class,
//        'MigrateFresh' => FreshCommand::class,
//        'MigrateInstall' => InstallCommand::class,
//        'MigrateRefresh' => RefreshCommand::class,
//        'MigrateReset' => ResetCommand::class,
//        'MigrateRollback' => RollbackCommand::class,
//        'MigrateStatus' => StatusCommand::class,
    ];

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);

        foreach($this->aliases as $key => $alias) {
            $this->aliases[$key] = $this->postfixCommandNamespace($alias);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateMakeCommand::class,
                MigrationsConvertCommand::class
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCreator();

        $this->registerCommands($this->commands);
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton($this->aliases['Repository'], function ($app) {
            $table = $app['config']['database.migrations'];

            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }


    /**
     * Register the migrator service.
     *
     * @return void
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton($this->aliases['Migrator'], function ($app) {
            $repository = $app[$this->aliases['Repository']];

            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });
    }

    protected function registerCreator()
    {
        $this->app->singleton($this->aliases['Creator'], function ($app) {
            $customStubPath = __DIR__ . '/../Migrations/stubs';

            return new MigrationCreator($app['files'], $customStubPath);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateMakeCommand()
    {

        $this->app->singleton($this->commands['MigrateMake'], function ($app) {
            $creator = $app[$this->postfixCommandNamespace('migration.creator')];

            $composer = $app['composer'];

            return new MigrateMakeCommand($creator, $composer);
        });
    }

    protected function registerMigrationsConvertCommand(): void
    {
        $this->app->singleton($this->commands['MigrationsConvert'], function ($app) {
            return new MigrationsConvertCommand($app['migrator']);
        });
    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return array_values($this->aliases);
    }
}
