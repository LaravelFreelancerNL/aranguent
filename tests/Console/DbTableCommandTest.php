<?php

test('db:table', function () {
    $this->artisan('db:table')
        ->expectsQuestion('Which table would you like to inspect?', 'children')
        ->expectsOutputToContain('children')
        ->expectsOutputToContain('Edge')
        ->expectsOutputToContain('User Keys Allowed')
        ->expectsOutputToContain('Key Type')
        ->expectsOutputToContain('Last Used Key')
        ->expectsOutputToContain('Wait For Sync')
        ->expectsOutputToContain('Columns')
        ->expectsOutputToContain('Size Estimate')
        ->expectsOutputToContain('primary _key')
        ->expectsOutputToContain('edge')
        ->assertSuccessful();
});

test('db:table children', function () {
    $this->artisan('db:table', ['table' => 'children'])
        ->expectsOutputToContain('children')
        ->expectsOutputToContain('Edge')
        ->expectsOutputToContain('User Keys Allowed')
        ->expectsOutputToContain('Key Type')
        ->expectsOutputToContain('Last Used Key')
        ->expectsOutputToContain('Wait For Sync')
        ->expectsOutputToContain('Columns')
        ->expectsOutputToContain('Size Estimate')
        ->expectsOutputToContain('primary _key')
        ->expectsOutputToContain('edge')
        ->assertSuccessful();
});

test('db:table characters', function () {
    $this->artisan('db:table', ['table' => 'characters'])
        ->expectsOutputToContain('characters')
        ->expectsOutputToContain('Vertex')
        ->expectsOutputToContain('User Keys Allowed')
        ->expectsOutputToContain('Key Type')
        ->expectsOutputToContain('Last Used Key')
        ->expectsOutputToContain('Wait For Sync')
        ->expectsOutputToContain('Columns')
        ->expectsOutputToContain('Size Estimate')
        ->expectsOutputToContain('primary _key')
        ->expectsOutputToContain('full_name')
        ->assertSuccessful();
});

test('db:table _job', function () {
    $this->artisan('db:table', ['table' => '_jobs'])
        ->expectsOutputToContain('_jobs')
        ->expectsOutputToContain('Vertex')
        ->expectsOutputToContain('User Keys Allowed')
        ->expectsOutputToContain('Key Type')
        ->expectsOutputToContain('Last Used Key')
        ->expectsOutputToContain('Wait For Sync')
        ->expectsOutputToContain('Columns')
        ->expectsOutputToContain('Size Estimate')
        ->expectsOutputToContain('primary _key')
        ->assertSuccessful();
});
