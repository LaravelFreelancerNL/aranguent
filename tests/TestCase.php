<?php

namespace Tests;

use ArangoClient\Schema\SchemaManager;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use LaravelFreelancerNL\Aranguent\AranguentServiceProvider;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\InteractsWithDatabase;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\PreparesTestingTransactions;
use LaravelFreelancerNL\Aranguent\Testing\DatabaseTransactions;
use LaravelFreelancerNL\Aranguent\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use TestSetup\TestConfig;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use interactsWithDatabase;
    use PreparesTestingTransactions;

    protected ?ConnectionInterface $connection;

    protected bool $dropViews = true;

    protected bool $realPath = true;

    protected array $migrationPaths = [];

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected SchemaManager $schemaManager;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            AranguentServiceProvider::class,
        ];
    }

    /**
     * Ignore package discovery from.
     *
     * @return array<int, string>
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return [];
    }

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<string, class-string<\Illuminate\Support\Facades\Facade>>
     */
    protected function getPackageAliases($app)
    {
        return [
            'Aranguent' => 'LaravelFreelancerNL\Aranguent',
        ];
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->migrationPaths = [
            realpath(__DIR__ . '/../TestSetup/Database/Migrations'),
            realpath(__DIR__ . '/../vendor/orchestra/testbench-core/laravel/migrations/'),
        ];
        $this->setTransactionCollections([
            'write' => [
                'cache',
                'cache_locks',
                'characters',
                'children',
                'failed_jobs',
                'houses',
                'job_batches',
                'jobs',
                'locations',
                'migrations',
                'password_reset_tokens',
                'sessions',
                'tags',
                'taggables',
                'users',
            ]]);

        parent::setUp();

        $this->connection = DB::connection();

        //Convert orchestra migrations
        $this->artisan(
            'convert:migrations',
            ['--realpath' => true, '--path' => __DIR__ . '/../vendor/orchestra/testbench-core/laravel/migrations/'],
        )->run();
    }

    /**
     * Boot the testing helper traits.
     *
     * @internal
     *
     * @param  array<class-string, class-string>  $uses
     * @return array<class-string, class-string>
     */
    protected function setUpTheTestEnvironmentTraits(array $uses): array
    {
        if (isset($uses[WithWorkbench::class])) {
            $this->setUpWithWorkbench(); // @phpstan-ignore-line
        }

        $this->setUpDatabaseRequirements(function () use ($uses) {
            if (isset($uses[RefreshDatabase::class])) {
                $this->refreshDatabase(); // @phpstan-ignore-line
            }

            if (isset($uses[DatabaseMigrations::class])) {
                $this->runDatabaseMigrations(); // @phpstan-ignore-line
            }

            if (isset($uses[DatabaseTruncation::class])) {
                $this->truncateDatabaseTables(); // @phpstan-ignore-line
            }
        });

        if (isset($uses[DatabaseTransactions::class])) {
            $this->beginDatabaseTransaction(); // @phpstan-ignore-line
        }

        if (isset($uses[WithoutMiddleware::class])) {
            $this->disableMiddlewareForAllTests(); // @phpstan-ignore-line
        }

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker(); // @phpstan-ignore-line
        }

        LazyCollection::make(static function () use ($uses) {
            foreach ($uses as $use) {
                yield $use;
            }
        })
            ->reject(function ($use) {
                /** @var class-string $use */
                return $this->setUpTheTestEnvironmentTraitToBeIgnored($use);
            })->map(static function ($use) {
                /** @var class-string $use */
                return class_basename($use);
            })->each(function ($traitBaseName) {
                /** @var string $traitBaseName */
                if (method_exists($this, $method = 'setUp' . $traitBaseName)) {
                    $this->{$method}();
                }

                if (method_exists($this, $method = 'tearDown' . $traitBaseName)) {
                    $this->beforeApplicationDestroyed(function () use ($method) {
                        $this->{$method}();
                    });
                }
            });

        return $uses;
    }


    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        TestConfig::set($app);
    }

    public function clearDatabase()
    {
        $collections  = $this->schemaManager->getCollections(true);
        foreach($collections as $collection) {
            $this->schemaManager->deleteCollection($collection->name);
        }
    }

    protected function skipTestOnArangoVersionsBefore(string $version)
    {
        if (version_compare(getenv('ARANGODB_VERSION'), $version, '<')) {
            $this->markTestSkipped('This test does not support ArangoDB versions before ' . $version);
        }
    }

    protected function skipTestOn(string $software, string $operator = '<', string $version)
    {
        $currentVersion = getenv('matrix.' . $software);
        if (! $currentVersion) {
            $currentVersion = getenv(strtoupper($software . '_VERSION'));
        }

        if (!$currentVersion) {
            return;
        }

        if (version_compare($currentVersion, $version, $operator)) {
            $this->markTestSkipped('This test does not support ' . ucfirst($software) . ' versions ' . $operator . ' ' . $version);
        }
    }
}
