<?php

declare(strict_types=1);

use LaravelFreelancerNL\Aranguent\Schema\Blueprint;

test('creating table with default key generator', function () {
    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {});

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('traditional');

    Schema::drop('white_walkers');
});

test('creating table with different default key generator', function () {
    Config::set('arangodb.schema.keyOptions.type', 'padded');
    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {});

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('padded');

    Schema::drop('white_walkers');
});

test('creating table with autoincrement key generator', function () {
    Config::set('arangodb.schema.key_handling.use_traditional_over_autoincrement', false);

    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {
        $table->increments('id');
    });

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('autoincrement');

    Schema::drop('white_walkers');
    Config::set('arangodb.schema.key_handling.use_traditional_over_autoincrement', true);
});

test('creating table with autoIncrement offset', function () {
    Config::set('arangodb.schema.key_handling.use_traditional_over_autoincrement', false);

    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {
        $table->string('id')->autoIncrement()->from(5);
    });

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('autoincrement');
    expect($collectionProperties->keyOptions->offset)->toBe(5);

    Schema::drop('white_walkers');
});

test('create table with uuid key generator', function () {
    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {
        $table->uuid('id');
    });

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('uuid');

    Schema::drop('white_walkers');
});

test('table options override column key generator', function () {
    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {
        $table->uuid('id');
    }, [
        'keyOptions' => [
            'type' => 'padded',
        ],
    ]);

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('padded');

    Schema::drop('white_walkers');
});

test('table options override default key generator', function () {
    $schema = DB::connection()->getSchemaBuilder();

    $schema->create('white_walkers', function (Blueprint $table) use (& $creating) {}, [
        'keyOptions' => [
            'type' => 'padded',
            'allowUserKeys' => false,
        ],
    ]);

    $schemaManager = $this->connection->getArangoClient()->schema();

    $collectionProperties = $schemaManager->getCollectionProperties('white_walkers');

    expect($collectionProperties->keyOptions->type)->toBe('padded');
    expect($collectionProperties->keyOptions->allowUserKeys)->toBeFalse();

    Schema::drop('white_walkers');
});
