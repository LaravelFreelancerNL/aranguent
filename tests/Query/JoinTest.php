<?php

use Illuminate\Support\Facades\DB;
use LaravelFreelancerNL\Aranguent\Query\JoinClause;

test('cross join', function () {
    $characters = DB::table('characters')
        ->crossJoin('locations')
        ->get();

    expect($characters)->toHaveCount(387);
});

test('join', function () {
    $query = DB::table('characters')
        ->join('locations', 'characters.residence_id', '=', 'locations.id')
        ->where('residence_id', '=', 'winterfell');

    $characters = $query->get();

    expect($characters)->toHaveCount(15);
    expect($characters[0]->id)->toEqual('winterfell');
});

test('join with specific selection', function () {
    $query = DB::table('characters')
        ->select('characters.surname', 'locations.name')
        ->join('locations', 'characters.residence_id', '=', 'locations.id')
        ->where('residence_id', '=', 'winterfell');

    $characters = $query->get();

    expect($characters)->toHaveCount(15);
    expect($characters[0]->name)->toEqual('Winterfell');
});


test('joinSub', function () {
    $locations = DB::table('locations')
        ->select()
        ->whereColumn('led_by', '=', 'characters.id');

    $builder = DB::table('characters')
        ->joinSub($locations, 'leads_locations', function (JoinClause $join) {
            $join->on('characters.id', '=', 'leads_locations.led_by');
        });

    $characters = $builder->get();

    expect($characters)->toHaveCount(7);
    expect($characters[0]->age)->toEqual($this->tableCount);
    expect($characters[0]->surname)->toEqual('Targaryen');
});


test('leftJoin', function () {
    $query = DB::table('characters')
        ->leftJoin('locations', 'characterDoc.residence_id', '=', 'locationDoc._key');
    $characters = $query->get();

    $charactersWithoutResidence = DB::table('characters')
        ->whereNull('residence_id')
        ->get();

    expect($characters)->toHaveCount(43);
    expect($characters[0]->id)->toEqual('winterfell');
    expect($characters[0]->age)->toEqual(41);
    expect($characters[0]->surname)->toEqual('Stark');
    expect($charactersWithoutResidence)->toHaveCount(10);
});

test('leftJoinSub', function () {
    $locations = DB::table('locations')
        ->where('name', '=', "King's Landing");

    $query = DB::table('characters')
        ->select('characters.*', 'leads_locations.*')
        ->where('surname', 'Lannister')
        ->leftJoinSub($locations, 'leads_locations', function (JoinClause $join) {
            $join->on('characters.id', '=', 'leads_locations.led_by');
            // needs to be set on the join query
        });

    $characters = $query->get();

    expect($characters)->toHaveCount(4);
    expect($characters->where('id', 'king-s-landing')->first())->toHaveKeys([
        'capturable_id',
        'capturable_type',
        'coordinate',
        'led_by',
    ]);
});

test('leftJoinSub with selection of attributes', function () {
    $locations = DB::table('locations')
        ->where('name', '=', "King's Landing");

    $query = DB::table('characters')
        ->select('characters.*', 'leads_locations.coordinate')
        ->where('surname', 'Lannister')
        ->leftJoinSub($locations, 'leads_locations', function (JoinClause $join) {
            $join->on('characters.id', '=', 'leads_locations.led_by');
        });

    $characters = $query->get();

    expect($characters)->toHaveCount(4);
    expect($characters->where('id', 'CerseiLannister')->first())->toHaveKeys([
        'coordinate',
    ]);
});

test('joinLateral', function () {
    $controlledLocations = DB::table('locations')
        ->whereColumn('locations.led_by', '==', 'characters.id')
        ->limit(3);

    $leadingLadies = DB::table('characters')
        ->joinLateral(
            $controlledLocations,
            'controlled_territory',
        )
        ->orderBy('name')
        ->get();

    expect($leadingLadies)->toHaveCount(6);
});


test('joinLateral with selected fields', function () {
    $controlledLocations = DB::table('locations')
        ->select('id as location_id', 'name as location_name')
        ->whereColumn('locations.led_by', '==', 'characters.id')
        ->orderBy('name')
        ->limit(3);

    $leadingLadies = DB::table('characters')
        ->select('id', 'name', 'controlled_territory.location_name as territory_name')
        ->joinLateral(
            $controlledLocations,
            'controlled_territory',
        )
        ->orderBy('name')
        ->get();

    expect($leadingLadies)->toHaveCount(6);

    expect(($leadingLadies[1])->name)->toBe('Cersei');
    expect(($leadingLadies[2])->name)->toBe('Daenerys');
    expect(($leadingLadies[2])->territory_name)->toBe('Astapor');
    expect(($leadingLadies[5])->name)->toBe('Sansa');
});
