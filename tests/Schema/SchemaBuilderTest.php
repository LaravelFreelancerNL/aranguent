<?php

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LaravelFreelancerNL\Aranguent\Connection;
use LaravelFreelancerNL\Aranguent\Facades\Schema;
use LaravelFreelancerNL\Aranguent\Exceptions\QueryException;
use LaravelFreelancerNL\Aranguent\Schema\Blueprint;
use LaravelFreelancerNL\Aranguent\Schema\Builder;
use LaravelFreelancerNL\Aranguent\Schema\Grammar;
use Mockery as M;
use Tests\Setup\ClassStubs\CustomBlueprint;

use TiMacDonald\Log\LogEntry;
use TiMacDonald\Log\LogFake;

afterEach(function () {
    M::close();
});

test('create with custom blueprint', function () {
    $schema = DB::connection()->getSchemaBuilder();
    $schema->blueprintResolver(function ($table, $callback) {
        return new CustomBlueprint($table, $callback);
    });
    $schema->create('characters', function (Blueprint $table) {
        expect($table)->toBeInstanceOf(CustomBlueprint::class);
    });

    refreshDatabase();
});

test('has table', function () {
    expect(Schema::hasTable('locations'))->toBeTrue();
    expect(Schema::hasTable('dummy'))->toBeFalse();
});

test('has table throws on none existing database', function () {
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

test('drop all tables', function () {
    $initialTables = Schema::getAllTables();

    Schema::dropAllTables();

    $tables = Schema::getAllTables();

    expect(count($initialTables))->toEqual(15);
    expect(count($tables))->toEqual(0);

    refreshDatabase();
});

test('hasColumn', function () {
    expect(Schema::hasColumn('characters', 'xname'))->toBeFalse();
    expect(Schema::hasColumn('characters', 'name'))->toBeTrue();
});

test('hasColumns', function () {
    expect(Schema::hasColumn('characters', ['name', 'xname']))->toBeFalse();
    expect(Schema::hasColumn('characters', ['name', 'alive']))->toBeTrue();
});

test('create view', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('search')) {
        Schema::createView('search', []);
    }
    $view = $schemaManager->getView('search');

    expect($view->name)->toEqual('search');

    $schemaManager->deleteView('search');
});

test('get view', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('search')) {
        Schema::createView('search', []);
    }
    $view = Schema::getView('search');

    expect($view->name)->toEqual('search');

    $schemaManager->deleteView('search');
});

test('get all views', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('pages')) {
        Schema::createView('pages', []);
    }
    if (!$schemaManager->hasView('products')) {
        Schema::createView('products', []);
    }
    if (!$schemaManager->hasView('search')) {
        Schema::createView('search', []);
    }

    $views = Schema::getAllViews();

    expect($views)->toHaveCount(5);
    expect($views[0]->name)->toBe('house_search_alias_view');
    expect($views[1]->name)->toBe('house_view');
    expect($views[2]->name)->toBe('pages');
    expect($views[3]->name)->toBe('products');
    expect($views[4]->name)->toBe('search');

    $schemaManager->deleteView('search');
    $schemaManager->deleteView('pages');
    $schemaManager->deleteView('products');
});

test('edit view', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('search')) {
        Schema::createView('search', []);
    }
    Schema::editView('search', ['consolidationIntervalMsec' => 5]);

    $properties = $schemaManager->getViewProperties('search');

    expect($properties->consolidationIntervalMsec)->toEqual(5);

    $schemaManager->deleteView('search');
});

test('rename view', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('search')) {
        Schema::createView('search', []);
    }
    Schema::renameView('search', 'find');

    $view = $schemaManager->getView('find');
    $schemaManager->deleteView('find');

    try {
        $schemaManager->getView('search');
    } catch (\ArangoClient\Exceptions\ArangoException $e) {
        $this->assertTrue(true);
    }

    expect($view->name)->toEqual('find');
});

test('drop view', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('search')) {
        Schema::createView('search', []);
    }
    Schema::dropView('search');

    $schemaManager->getView('search');
})->throws(ArangoException::class);

test('drop all views', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasView('products')) {
        Schema::createView('products', []);
    }
    if (!$schemaManager->hasView('pages')) {
        Schema::createView('pages', []);
    }
    Schema::dropAllViews();

    $this->assertFalse($schemaManager->hasView('products'));
    $this->assertFalse($schemaManager->hasView('search'));

    refreshDatabase();
});

test('create database', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    $databaseName = 'aranguent__test_dummy';
    $result = Schema::createDatabase($databaseName);

    expect($result)->toBeTrue();
    expect($schemaManager->hasDatabase($databaseName))->toBeTrue();

    $schemaManager->deleteDatabase($databaseName);

    refreshDatabase();
});

test('drop database if exists', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    $databaseName = 'aranguent__test_dummy';
    Schema::createDatabase($databaseName);

    $result = Schema::dropDatabaseIfExists($databaseName);

    expect($result)->toBeTrue();
    expect($schemaManager->hasDatabase($databaseName))->toBeFalse();

    refreshDatabase();
});

test('drop database if exists none existing db', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    $databaseName = 'aranguent__test_dummy';

    expect($schemaManager->hasDatabase($databaseName))->toBeFalse();

    $result = Schema::dropDatabaseIfExists($databaseName);

    expect($result)->toBeTrue();

    refreshDatabase();
});

test('get connection', function () {
    expect(Schema::getConnection())->toBeInstanceOf(Connection::class);
});

test('createAnalyzer', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('myAnalyzer')) {
        Schema::createAnalyzer('myAnalyzer', [
            'type' => 'identity'
        ]);
    }
    $analyzer = $schemaManager->getAnalyzer('myAnalyzer');

    expect($analyzer->name)->toEqual('aranguent__test::myAnalyzer');

    $schemaManager->deleteAnalyzer('myAnalyzer');
});

test('getAllAnalyzers', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    $analyzers = Schema::getAllAnalyzers();

    expect($analyzers)->toHaveCount(13);
});

test('replaceAnalyzer', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('myAnalyzer')) {
        Schema::createAnalyzer('myAnalyzer', [
            'type' => 'identity'
        ]);
    }

    Schema::replaceAnalyzer('myAnalyzer', [
        'type' => 'identity'
    ]);

    $schemaManager->deleteAnalyzer('myAnalyzer');
});

test('dropAnalyzer', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('myAnalyzer')) {
        Schema::createAnalyzer('myAnalyzer', [
            'type' => 'identity'
        ]);
    }
    Schema::dropAnalyzer('myAnalyzer');

    $schemaManager->getAnalyzer('myAnalyzer');
})->throws(ArangoException::class);

test('dropAnalyzerIfExists true', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('myAnalyzer')) {
        Schema::createAnalyzer('myAnalyzer', [
            'type' => 'identity'
        ]);
    }
    Schema::dropAnalyzerIfExists('myAnalyzer');

    $schemaManager->getAnalyzer('myAnalyzer');
})->throws(ArangoException::class);

test('dropAnalyzerIfExists false', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    Schema::dropAnalyzerIfExists('none-existing-analyzer');
});

test('Silently fails unsupported functions', function () {
    Schema::nonExistingFunction('none-existing-analyzer');
})->throwsNoExceptions();

// Removed the log fake dependency for now as it usually lags behind new Laravel releases
test('Unsupported functions are logged', function () {
    $this->skipTestOn('laravel', '>', '3.10');

    LogFake::bind();

    Schema::nonExistingFunction('none-existing-analyzer');

    Log::assertLogged(
        fn(LogEntry $log) => $log->level === 'warning'
    );
});
