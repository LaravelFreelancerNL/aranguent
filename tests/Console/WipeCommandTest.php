<?php

use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->schemaManager = $this->connection->getArangoClient()->schema();
});

afterEach(function () {
    $path = [
        realpath(__DIR__ . '/../../TestSetup/Database/Migrations'),
    ];

    $this->artisan('migrate:fresh', [
        '--path' => [
            database_path('migrations'),
            realpath(__DIR__ . '/../../TestSetup/Database/Migrations'),
            realpath(__DIR__ . '/../../vendor/orchestra/testbench-core/laravel/migrations/'),
        ],
        '--realpath' => true,
        '--seed' => true,
        '--seeder' => DatabaseSeeder::class,
        '--drop-all' => true,
    ]);
});

test('db:wipe', function () {
    $this->artisan('db:wipe')->assertExitCode(0);

    $views = $this->schemaManager->getViews();
    expect(count($views))->toBe(2);
});

test('db:wipe --database=arangodb', function () {
    $this->artisan('db:wipe', [
        '--database' => 'arangodb',
    ])->assertExitCode(0);

    $views = $this->schemaManager->getViews();
    expect(count($views))->toBe(2);
});

test('migrate:fresh --database=none', function () {
    $this->artisan('db:wipe', [
        '--database' => 'none',
    ])->assertExitCode(0);

    $views = $this->schemaManager->getViews();
    expect(count($views))->toBe(2);
})->throws(InvalidArgumentException::class);

test('db:wipe --drop-views', function () {
    $this->artisan('db:wipe', [
        '--drop-views' => true,
    ])->assertExitCode(0);

    $views = $this->schemaManager->getViews();
    expect(count($views))->toBe(0);
});

test('db:wipe --drop-analyzers', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('dropMyAnalyzer')) {
        Schema::createAnalyzer(
            'dropMyAnalyzer',
            'identity',
        );
    }

    $this->artisan('db:wipe', [
        '--drop-analyzers' => true,
    ])->assertExitCode(0);

    $analyzers = $this->schemaManager->getAnalyzers();
    expect(count($analyzers))->toBe(13);
});

test('db:wipe --drop-graphs', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasGraph('dropMyGraph')) {
        Schema::createGraph(
            'dropMyGraph',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
            ],
            true,
        );
    }


    $this->artisan('db:wipe', [
        '--drop-graphs' => true,
    ])->assertExitCode(0);

    $graphs = $this->schemaManager->getGraphs();
    expect(count($graphs))->toBe(0);
});

test('db:wipe --drop-all', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('dropMyAnalyzer')) {
        Schema::createAnalyzer(
            'dropMyAnalyzer',
            'identity',
        );
    }

    if (!$schemaManager->hasGraph('dropMyGraph')) {
        Schema::createGraph(
            'dropMyGraph',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
            ],
            true,
        );
    }

    if (!$schemaManager->hasView('dropViewTest')) {
        Schema::createView('dropViewTest', []);
    }

    $this->artisan('db:wipe', [
        '--drop-all' => true,
    ])->assertExitCode(0);

    $analyzers = $this->schemaManager->getAnalyzers();
    expect(count($analyzers))->toBe(13);

    $graphs = $this->schemaManager->getGraphs();
    expect(count($graphs))->toBe(0);

    $views = $this->schemaManager->getViews();
    expect(count($views))->toBe(0);
});

test('db:wipe --drop-types', function () {
    $this->artisan('db:wipe', [
        '--drop-types' => true,
    ])->assertExitCode(0);
})->throws('This database driver does not support dropping all types.');
