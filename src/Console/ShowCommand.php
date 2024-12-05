<?php

namespace LaravelFreelancerNL\Aranguent\Console;

use ArangoClient\Exceptions\ArangoException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Console\ShowCommand as IlluminateShowCommand;
use Illuminate\Database\Schema\Builder as IlluminateSchemaBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use LaravelFreelancerNL\Aranguent\Connection;
use LaravelFreelancerNL\Aranguent\Schema\Builder as SchemaBuilder;

class ShowCommand extends IlluminateShowCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:show {--database= : The database connection}
                {--json : Output the database information as JSON}
                {--counts : Show the table row count }
                {--views : Show the database views }
                {--analyzers : Show the database analyzers (ArangoDB)}
                {--graphs : Show the database named graphs (ArangoDB)}
                {--system : Show the database system tables (ArangoDB)}
                {--types : Show the user defined types (Postgresql)}
                {--all : Show tables, analyzers, graphs and views (ArangoDB)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display information about the given database';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connections
     * @return int
     */
    public function handle(ConnectionResolverInterface $connections)
    {
        $connection = $connections->connection($database = $this->input->getOption('database'));

        assert($connection instanceof Connection);

        if ($connection->getDriverName() !== 'arangodb') {
            return parent::handle($connections);
        }

        $schema = $connection->getSchemaBuilder();

        $fullVersion = $this->getFullVersion($connection);

        $data = [
            'platform' => [
                'config' => $this->getConfigFromDatabase($database),
                'server' => $fullVersion->server ?? 'arango',
                'license' => $fullVersion->license ?? 'unknown',
                'name' => $connection->getDriverTitle(),
                'connection' => $connection->getName(),
                'version' => $fullVersion->version ?? 'unknown',
                'isSystemDatabase' =>  $this->getDatabaseInfo($connection),
                'open_connections' => $connection->threadCount(),
            ],
            'tables' => $this->tables($connection, $schema),
        ];

        $data['views'] = $this->views($connection, $schema);

        $data['analyzers'] = $this->analyzers($schema);

        $data['graphs'] = $this->graphs($schema);

        $this->display($data, $connection);

        return 0;
    }

    /**
     * Render the database information.
     *
     * @param array<mixed> $data
     * @param Connection|null $connection
     * @return void
     */
    protected function display(array $data, ?Connection $connection = null)
    {
        $this->option('json') ? $this->displayJson($data) : $this->displayForCli($data, $connection);
    }


    /**
     * Get information regarding the tables within the database.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  IlluminateSchemaBuilder $schema
     * @return \Illuminate\Support\Collection
     */
    protected function tables(ConnectionInterface $connection, $schema)
    {
        assert($connection instanceof Connection);

        if ($connection->getDriverName() !== 'arangodb') {
            return parent::tables($connection, $schema);
        }

        assert($schema instanceof SchemaBuilder);

        // Get all tables
        $tables = collect(
            ($this->input->getOption('system')) ? $schema->getAllTables() : $schema->getTables(),
        )->sortBy('name');

        // Get per table statistics
        $tableStats = [];
        foreach ($tables as $table) {
            $tableStats[] = $schema->getTable($table->name);
        }

        return collect($tableStats)->map(fn($table) => [
            'table' => $table['name'],
            'size' => $table['figures']->documentsSize,
            'rows' => $this->option('counts')
                ? $table['count']
                : null,
        ]);
    }

    /**
     * Get information regarding the views within the database.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Schema\Builder  $schema
     * @return \Illuminate\Support\Collection
     */
    protected function views(ConnectionInterface $connection, IlluminateSchemaBuilder $schema)
    {
        assert($connection instanceof Connection);

        if ($connection->getDriverName() !== 'arangodb') {
            return parent::views($connection, $schema);
        }

        return collect($schema->getViews())
            ->map(fn($view) => [
                'name' => $view->name,
                'type' => $view->type,
            ]);
    }

    /**
     * Get information regarding the analyzers within the database.
     *
     * @param  SchemaBuilder $schema
     * @return \Illuminate\Support\Collection
     */
    protected function analyzers(SchemaBuilder $schema)
    {
        return collect($schema->getAnalyzers())
            ->map(fn($analyzer) => [
                'name' => $analyzer->name,
                'type' => $analyzer->type,
            ]);
    }

    /**
     * Get information regarding the named graphs within the database.
     *
     * @param SchemaBuilder $schema
     * @return \Illuminate\Support\Collection
     */
    protected function graphs(SchemaBuilder $schema)
    {
        return collect($schema->getGraphs())
            ->map(fn($graph) => [
                'name' => $graph->name,
                'edgeDefinitions' => count($graph->edgeDefinitions),
            ]);
    }

    protected function getFullVersion(Connection $connection): object
    {
        $client = $connection->getArangoClient();

        assert($client !== null);

        return $client->admin()->getVersion();
    }

    /**
     * @throws ArangoException
     */
    protected function getDatabaseInfo(Connection $connection): bool
    {
        $client = $connection->getArangoClient();

        assert($client !== null);

        $info = $client->schema()->getCurrentDatabase();

        return $info->isSystem;
    }

    /**
     * @param mixed $views
     * @return void
     */
    public function displayViews(mixed $views): void
    {
        if (! $this->input->getOption('views') || $views->isEmpty()) {
            return;
        }

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>View</>',
            '<fg=green;options=bold>Type</>',
        );

        $views->each(fn($view) => $this->components->twoColumnDetail(
            $view['name'],
            $view['type'],
        ));

        $this->newLine();
    }

    /**
     * @param mixed $analyzers
     * @return void
     */
    public function displayAnalyzers(mixed $analyzers): void
    {
        if (! $this->input->getOption('analyzers') || $analyzers->isEmpty()) {
            return;
        }

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>Analyzers</>',
            '<fg=green;options=bold>Type</>',
        );

        $analyzers->each(fn($analyzer) => $this->components->twoColumnDetail(
            $analyzer['name'],
            $analyzer['type'],
        ));

        $this->newLine();
    }
    /**
     * @param mixed $graphs
     * @return void
     */
    public function displayGraphs(mixed $graphs): void
    {
        if (! $this->input->getOption('graphs') || $graphs->isEmpty()) {
            return;
        }

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>Graphs</>',
            '<fg=green;options=bold>Edge Definitions</>',
        );

        $graphs->each(fn($graph) => $this->components->twoColumnDetail(
            $graph['name'],
            $graph['edgeDefinitions'],
        ));

        $this->newLine();
    }

    /**
     * Render the database information formatted for the CLI.
     *
     * @param array<mixed> $data
     * @param Connection|null $connection
     * @return void
     */
    protected function displayForCli(array $data, ?Connection $connection = null)
    {
        if ($connection && $connection->getDriverName() !== 'arangodb') {
            parent::displayForCli($data);
            return;
        }

        $platform = $data['platform'];
        $tables = $data['tables'];
        $analyzers = $data['analyzers'] ?? null;
        $views = $data['views'] ?? null;
        $graphs = $data['graphs'] ?? null;

        $this->newLine();

        $this->components->twoColumnDetail('<fg=green;options=bold>ArangoDB (' . ucfirst($platform['license']) . ' Edition)</>', '<fg=green;options=bold>' . $platform['version'] . '</>');
        $this->components->twoColumnDetail('Connection', $platform['connection']);
        $this->components->twoColumnDetail('Database', Arr::get($platform['config'], 'database'));
        $this->components->twoColumnDetail('Host', Arr::get($platform['config'], 'host'));
        $this->components->twoColumnDetail('Port', Arr::get($platform['config'], 'port'));
        $this->components->twoColumnDetail('Username', Arr::get($platform['config'], 'username'));
        $this->components->twoColumnDetail('URL', Arr::get($platform['config'], 'url') ?? Arr::get($platform['config'], 'endpoint'));
        $this->components->twoColumnDetail('Open Connections', $platform['open_connections']);
        $this->components->twoColumnDetail('Analyzers', $analyzers->count());
        $this->components->twoColumnDetail('Views', $views->count());
        $this->components->twoColumnDetail('Named Graphs', $graphs->count());
        $this->components->twoColumnDetail('Tables', $tables->count());

        $tableSizeSum = $tables->sum('size');
        if ($tableSizeSum) {
            $this->components->twoColumnDetail('Total Size Estimate', Number::fileSize($tableSizeSum, 2));
        }

        $this->newLine();

        if ($tables->isNotEmpty()) {
            $this->components->twoColumnDetail(
                '<fg=green;options=bold>Table</>',
                'Size Estimate' . ($this->option('counts') ? ' <fg=gray;options=bold>/</> <fg=yellow;options=bold>Rows</>' : ''),
            );

            $tables->each(function ($table) {
                $tableSize = is_null($table['size']) ? null : Number::fileSize($table['size'], 2);

                $this->components->twoColumnDetail(
                    $table["table"],
                    ($tableSize ?? '—') . ($this->option('counts') ? ' <fg=gray;options=bold>/</> <fg=yellow;options=bold>' . Number::format($table['rows']) . '</>' : ''),
                );
            });

            $this->newLine();
        }

        $this->displayViews($views);

        $this->displayAnalyzers($analyzers);

        $this->displayGraphs($graphs);
    }
}
