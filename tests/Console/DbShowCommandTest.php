<?php

test('db:show', function () {
    $this->artisan('db:show')
        ->expectsOutputToContain('Connection')
        ->expectsOutputToContain('Database')
        ->expectsOutputToContain('Analyzers')
        ->expectsOutputToContain('Views')
        ->expectsOutputToContain('Named Graphs')
        ->expectsOutputToContain('characters')
        ->expectsOutputToContain('users')
        ->assertSuccessful();
});

test('db:show --counts', function () {
    $this->artisan(
        'db:show',
        [
            '--counts' => true,
        ],
    )
        ->expectsOutputToContain('Size Estimate / Rows')
        ->expectsOutputToContain('/ 43')
        ->assertSuccessful();
});


test('db:show --analyzers', function () {
    $this->artisan(
        'db:show',
        [
            '--analyzers' => true,
        ],
    )
        ->expectsOutputToContain('Analyzers')
        ->expectsOutputToContain('text_nl')
        ->expectsOutputToContain('identity')
        ->assertSuccessful();
});

test('db:show --views', function () {
    $this->artisan(
        'db:show',
        [
            '--views' => true,
        ],
    )
        ->expectsOutputToContain('View')
        ->expectsOutputToContain('house_search_alias_view')
        ->expectsOutputToContain('arangosearch')
        ->assertSuccessful();
});

test('db:show --graphs', function () {
    $this->schemaManager = $this->connection->getArangoClient()->schema();

    $this->schemaManager->createGraph(
        'relatives',
        [
            'edgeDefinitions' => [
                [
                    'collection' => 'children',
                    'from' => ['characters'],
                    'to' => ['characters'],
                ],
            ],
        ],
    );

    $this->artisan(
        'db:show',
        [
            '--graphs' => true,
        ],
    )
        ->expectsOutputToContain('Graphs')
        ->expectsOutputToContain('relatives')
        ->assertSuccessful();


    $this->schemaManager->deleteGraph('relatives');
});

test('db:show --system', function () {
    $this->artisan(
        'db:show',
        [
            '--system' => true,
        ],
    )
        ->expectsOutputToContain('_analyzers')
        ->expectsOutputToContain('_jobs')
        ->expectsOutputToContain('_queues')
        ->assertSuccessful();
});
