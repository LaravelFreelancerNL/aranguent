<?php

declare(strict_types=1);

use LaravelFreelancerNL\Aranguent\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->schemaManager = $this->connection->getArangoClient()->schema();
});

test('migrate:install', function () {
    $this->clearDatabase();

    $this->artisan('migrate:install')->assertExitCode(0);

    $collections = $this->schemaManager->getCollections(true);

    expect(count($collections))->toBe(1);
    expect($collections[0]->name)->toBe('migrations');
});

test('migrate:install --database=arangodb', function () {
    $this->clearDatabase();

    $this->artisan('migrate:install', ['--database' => 'arangodb'])->assertExitCode(0);

    $collections = $this->schemaManager->getCollections(true);

    expect(count($collections))->toBe(1);
    expect($collections[0]->name)->toBe('migrations');
});

test('migrate:install --database=none', function () {
    $this->artisan('migrate:install', ['--database' => 'none'])->assertExitCode(0);
})->throws(InvalidArgumentException::class);
