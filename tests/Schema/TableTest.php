<?php

use LaravelFreelancerNL\Aranguent\Exceptions\QueryException;
use LaravelFreelancerNL\Aranguent\Schema\Blueprint;

test('creating table', function () {
    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {});

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    Schema::drop('white_walkers');
});

test('hasTable', function () {
    expect(Schema::hasTable('locations'))->toBeTrue();
    expect(Schema::hasTable('dummy'))->toBeFalse();
});

test('hasTable throws on none existing database', function () {
    DB::purge();
    $newDatabase = 'otherDatabase';
    config()->set('database.connections.arangodb.database', $newDatabase);

    $this->expectException(QueryException::class);

    Schema::hasTable('dummy');
});

test('rename', function () {
    $result = Schema::rename('characters', 'people');

    expect($result)->toBeTrue();
    expect(Schema::hasTable('characters'))->toBeFalse();
    expect(Schema::hasTable('people'))->toBeTrue();

    Schema::rename('people', 'characters');

    refreshDatabase();
});

test('dropAllTables', function () {
    $initialTables = Schema::getTables();

    Schema::dropAllTables();

    $tables = Schema::getTables();

    expect(count($initialTables))->toEqual($this->tableCount);
    expect(count($tables))->toEqual(0);

    refreshDatabase();
});
