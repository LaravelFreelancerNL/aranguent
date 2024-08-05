<?php

use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->schemaManager = $this->connection->getArangoClient()->schema();
});

test('migrate:refresh', function () {
    $path = [
        realpath(__DIR__ . '/../../TestSetup/Database/Migrations'),
    ];

    $this->artisan('migrate:refresh', [
        '--path' => [
            database_path('migrations'),
            realpath(__DIR__ . '/../../TestSetup/Database/Migrations'),
            realpath(__DIR__ . '/../../vendor/orchestra/testbench-core/laravel/migrations/'),
        ],
        '--realpath' => true,
        '--seed' => true,
        '--seeder' => DatabaseSeeder::class,

    ])->assertExitCode(0);

    $collections = $this->schemaManager->getCollections(true);
    expect(count($collections))->toBe(16);
});

test('migrate:refresh --database=arangodb', function () {
    $path = [
        realpath(__DIR__ . '/../../TestSetup/Database/Migrations'),
    ];

    $this->artisan('migrate:refresh', [
        '--path' => [
            database_path('migrations'),
            realpath(__DIR__ . '/../../TestSetup/Database/Migrations'),
            realpath(__DIR__ . '/../../vendor/orchestra/testbench-core/laravel/migrations/'),
        ],
        '--realpath' => true,
        '--seed' => true,
        '--seeder' => DatabaseSeeder::class,

    ])->assertExitCode(0);

    $collections = $this->schemaManager->getCollections(true);
    expect(count($collections))->toBe(16);
});

test('migrate:refresh --database=none', function () {
    $this->artisan('migrate:refresh', [
        '--database' => 'none',
    ])->assertExitCode(0);
})->throws(InvalidArgumentException::class);
