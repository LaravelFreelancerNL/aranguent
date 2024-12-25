<?php

declare(strict_types=1);

test('model:show', function () {
    $this->artisan('model:show')
        ->assertFailed();
})->throws('Not enough arguments (missing: "model")');

test('model:show \\TestSetup\\Models\\Character', function () {
    $this->artisan('model:show', ['model' => '\\TestSetup\\Models\\Character'])
        ->expectsOutputToContain('arangodb')
        ->expectsOutputToContain('characters')
        ->expectsOutputToContain('traditional')
        ->expectsOutputToContain('computed')
        ->assertSuccessful();
});

test('model:show \\TestSetup\\Models\\Character --json', function () {
    $this->artisan('model:show', ['model' => '\\TestSetup\\Models\\Character'])
        ->expectsOutputToContain('arangodb')
        ->expectsOutputToContain('characters')
        ->expectsOutputToContain('traditional')
        ->expectsOutputToContain('computed')
        ->assertSuccessful();
});

test('model:show \\TestSetup\\Models\\Child', function () {
    $this->artisan('model:show', ['model' => '\\TestSetup\\Models\\Child'])
        ->expectsOutputToContain('arangodb')
        ->expectsOutputToContain('children')
        ->expectsOutputToContain('traditional')
        ->doesntExpectOutput('computed')
        ->assertSuccessful();
});

test('model:show \\TestSetup\\Models\\House', function () {
    $this->artisan('model:show', ['model' => '\\TestSetup\\Models\\House'])
        ->expectsOutputToContain('arangodb')
        ->expectsOutputToContain('houses')
        ->expectsOutputToContain('traditional')
        ->doesntExpectOutput('computed')
        ->assertSuccessful();
});
