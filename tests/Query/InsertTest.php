<?php

use Illuminate\Support\Facades\DB;
use LaravelFreelancerNL\Aranguent\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('insert', function () {
    $characterData = [
        "id" => "LyannaStark",
        "name" => "Lyanna",
        "surname" => "Stark",
        "alive" => false,
        "age" => 25,
        "tags" => [],
        "residence_id" => "winterfell",
    ];
    DB::table('characters')->insert($characterData);

    $results = DB::table('characters')->get();

    expect($results->last()->tags)->toBeArray();
    expect($results->last()->tags)->toBeEmpty();
});


test('insert get id', function () {
    $builder = getBuilder();

    $builder->getConnection()->shouldReceive('execute')->once()->andReturn(1);
    $result = $builder->from('users')->insertGetId(['email' => 'foo']);
    expect($result)->toEqual(1);
});

test('insert or ignore inserts data', function () {
    $characterData = [
        "_key" => "LyannaStark",
        "name" => "Lyanna",
        "surname" => "Stark",
        "alive" => false,
        "age" => 25,
        "residence_id" => "winterfell",
    ];

    $result = DB::table('characters')
        ->where("name", "==", "Lyanna")
        ->get();

    expect($result->count())->toBe(0);


    DB::table('characters')->insertOrIgnore($characterData);

    $result = DB::table('characters')
        ->where("name", "=", "Lyanna")
        ->get();

    expect($result->count())->toBe(1);
});

test('insert or ignore doesnt error on duplicates', function () {
    $characterData = [
        "_key" => "LyannaStark",
        "name" => "Lyanna",
        "surname" => "Stark",
        "alive" => false,
        "age" => 25,
        "residence_id" => "winterfell",
    ];
    DB::table('characters')->insert($characterData);

    DB::table('characters')->insertOrIgnore($characterData);

    $result = DB::table('characters')
        ->where("name", "=", "Lyanna")
        ->get();

    expect($result->count())->toBe(1);
});

test('insert embedded empty array', function () {
    $characterData = [
        "_key" => "LyannaStark",
        "name" => "Lyanna",
        "surname" => "Stark",
        "alive" => false,
        "age" => 25,
        "residence_id" => "winterfell",
        "tags" => [],
    ];
    DB::table('characters')->insert($characterData);

    DB::table('characters')->insertOrIgnore($characterData);

    $result = DB::table('characters')
        ->where("name", "=", "Lyanna")
        ->get();

    expect($result->first()->tags)->toBeArray();
    expect($result->first()->tags)->toBeEmpty();
});

test('insert using', function () {
    // Let's give Baelish a user, what could possibly go wrong?
    $baelishes = DB::table('characters')
        ->where('surname', 'Baelish');

    DB::table('users')->insertUsing(['name', 'surname'], $baelishes);

    $user = DB::table('users')->first();

    expect($user->surname)->toBe('Baelish');
});
