<?php

use Illuminate\Database\Eloquent\Builder;
use LaravelFreelancerNL\Aranguent\Testing\DatabaseTransactions;
use TestSetup\Models\Character;
use TestSetup\Models\Location;

uses(
    DatabaseTransactions::class,
);

test('retrieve relation', function () {
    $location = Location::find('winterfell');
    $character = $location->capturable;

    expect($location->id)->toEqual('winterfell');
    expect($character->id)->toEqual($location->capturable_id);
    expect($character)->toBeInstanceOf(Character::class);
});

test('associate', function () {
    $location = Location::find('winterfell');
    $character = Character::find('TheonGreyjoy');

    $location->capturable()->associate($character);
    $location->save();
    $location = Location::find('winterfell');

    expect($location->capturable_id)->toEqual($character->id);
    expect($location->capturable)->toBeInstanceOf(Character::class);
});

test('with', function () {
    $location = Location::with('capturable')->find('winterfell');

    expect($location->capturable)->toBeInstanceOf(Character::class);
    expect($location->capturable->id)->toEqual('TheonGreyjoy');
});

test('whereHasMorph', function () {
    $locations = Location::whereHasMorph(
        'capturable',
        Character::class,
        function (Builder $query) {
            $query->where('_key', 'TheonGreyjoy');
        },
    )->get();

    expect(count($locations))->toEqual(1);
});

test('orWhereHasMorph', function () {
    $locations = Location::where(function (Builder $query) {
        $query->whereHasMorph(
            'capturable',
            Character::class,
            function (Builder $query) {
                $query->where('id', 'TheonGreyjoy');
            },
        )
            ->orWhereHasMorph(
                'capturable',
                Character::class,
                function (Builder $query) {
                    $query->where('id', 'DaenerysTargaryen');
                },
            );
    })->get();

    expect(count($locations))->toEqual(6);
});

test('whereMorphRelation ', function () {
    $locations = Location::whereMorphRelation(
        'capturable',
        Character::class,
        '_key',
        'TheonGreyjoy',
    )
    ->get();

    expect(count($locations))->toEqual(1);
});

test('orWhereMorphRelation', function () {
    $locations = Location::where(function (Builder $query) {
        $query->whereMorphRelation(
            'capturable',
            Character::class,
            'id',
            'TheonGreyjoy',
        )
        ->orWhereMorphRelation(
            'capturable',
            Character::class,
            'id',
            'DaenerysTargaryen',
        );
    })->get();

    expect($locations->count())->toEqual(6);
});

test('whereDoesntHaveMorph', function () {
    $locations = Location::whereDoesntHaveMorph(
        'capturable',
        Character::class,
        function (Builder $query) {
            $query->where('id', 'DaenerysTargaryen');
        },
    )->get();

    expect(count($locations))->toEqual(1);
});

test('orWhereDoesntHaveMorph', function () {
    $locations = Location::where(function (Builder $query) {
        $query->whereHasMorph(
            'capturable',
            Character::class,
            function (Builder $query) {
                $query->where('alive', true);
            },
        )
            ->orWhereDoesntHaveMorph(
                'capturable',
                Character::class,
                function (Builder $query) {
                    $query->where('age', '<', 20);
                },
            );
    })->get();

    expect(count($locations))->toEqual(6);
});


test('whereMorphDoesntHaveRelation', function () {
    $locations = Location::whereMorphDoesntHaveRelation(
        'capturable',
        Character::class,
        'id',
        'TheonGreyjoy',
    )->get();

    expect(count($locations))->toEqual(5);
});

test('orWhereMorphDoesntHaveRelation', function () {
    $locations = Location::where(function (Builder $query) {
        $query->whereMorphDoesntHaveRelation(
            'capturable',
            Character::class,
            'id',
            'DaenerysTargaryen',
        )
            ->orWhereMorphDoesntHaveRelation(
                'capturable',
                Character::class,
                'age',
                '<',
                20,
            );
    })->get();

    expect(count($locations))->toEqual(1);
});
