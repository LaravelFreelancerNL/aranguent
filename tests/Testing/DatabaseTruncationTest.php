<?php

declare(strict_types=1);

use LaravelFreelancerNL\Aranguent\Facades\Schema;
use LaravelFreelancerNL\Aranguent\Testing\DatabaseTruncation;
use TestSetup\Models\Character;

uses(DatabaseTruncation::class);

test('Ensure all tables are present', function () {
    $tables = Schema::getAllTables();

    expect(count($tables))->toEqual(15);
});


test('Ensure all characters are present', function () {
    $characters = Character::all();

    expect(count($characters))->toEqual(43);
});
