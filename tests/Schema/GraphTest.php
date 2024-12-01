<?php

declare(strict_types=1);

use ArangoClient\Exceptions\ArangoException;

function createGraph()
{
    Schema::createGraph(
        'myGraph',
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

test('graph CRUD', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasGraph('myGraph')) {
        createGraph();
    }

    $graphExists = $schemaManager->hasGraph('myGraph');
    expect($graphExists)->toBeTrue();

    $graph = $schemaManager->getGraph('myGraph');
    expect($graph->name)->toEqual('myGraph');

    $schemaManager->deleteGraph('myGraph');
    $graphExists = $schemaManager->hasGraph('myGraph');
    expect($graphExists)->toBeFalse();
});

test('getAllGraphs', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    $graphs = Schema::getAllGraphs();
    expect($graphs)->toHaveCount(0);

    if (!$schemaManager->hasGraph('myGraph')) {
        createGraph();
    }

    $graphs = Schema::getAllGraphs();
    expect($graphs)->toHaveCount(1);
});

test('dropGraph', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasGraph('myGraph')) {
        createGraph();
    }

    Schema::dropGraph('myGraph');

    $schemaManager->getGraph('myGraph');
})->throws(ArangoException::class);

test('dropGraphIfExists true', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();
    if (!$schemaManager->hasAnalyzer('myGraph')) {
        createGraph();
    }
    Schema::dropGraphIfExists('myGraph');

    $schemaManager->getGraph('myGraph');
})->throws(ArangoException::class);

test('dropGraphIfExists false', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    Schema::dropGraphIfExists('none-existing-graph');
});

test('dropAllGraphs', function () {
    $schemaManager = $this->connection->getArangoClient()->schema();

    $initialGraphs = Schema::getAllGraphs();

    Schema::createGraph('myGraph1');
    Schema::createGraph('myGraph2');

    $totalGraphs = Schema::getAllGraphs();

    Schema::dropAllGraphs();

    $endGraphs = Schema::getAllGraphs();

    expect(count($initialGraphs))->toBe(count($endGraphs));
    expect(count($initialGraphs))->toBe(count($totalGraphs) - 2);
});
