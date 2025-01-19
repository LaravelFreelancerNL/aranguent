<?php

use Illuminate\Database\Eloquent\Builder;
use LaravelFreelancerNL\Aranguent\Testing\DatabaseTransactions;
use TestSetup\Models\Character;
use TestSetup\Models\House;
use TestSetup\Models\Location;

uses(
    DatabaseTransactions::class,
);

test('has', function () {
    $characters = Character::has('leads')->get();
    expect(count($characters))->toEqual(3);
});

test('has, with minimum relation count', function () {
    $characters = Character::has('leads', '>=', 3)->get();
    expect(count($characters))->toEqual(1);
});

test('has morph', function () {
    $characters = Character::has('tags')->get();

    expect(count($characters))->toEqual(2);
});


test('orHas', function () {
    $characters = Character::has('leads')
        ->orHas('captured')
        ->get();

    expect(count($characters))->toEqual(4);
});

test('doesntHave', function () {
    $characters = Character::doesntHave('leads')->get();

    expect(count($characters))->toEqual(40);
});

test('orDoesntHave', function () {
    $characters = Character::where(function (Builder $query) {
        $query->doesntHave('leads')
            ->orDoesntHave('conquered');
    })
    ->get();

    $daenarys = $characters->first(function (Character $character) {
        return $character->id === 'DaenerysTargaryen';
    });

    expect(count($characters))->toEqual(42);
    expect($daenarys)->toBeNull();
});

test('whereHas', function () {
    $locations = Location::whereHas('leader', function (Builder $query) {
        $query->where('age', '<', 30);
    })
    ->distinct()
    ->pluck('led_by');

    expect($locations->count())->toBe(2);
    expect($locations[0])->toBe('DaenerysTargaryen');
    expect($locations[1])->toBe('SansaStark');
});

test('orWhereHas', function () {
    $locations = Location::where(function (Builder $query) {
        $query->whereHas('leader', function (Builder $query) {
            $query->where('age', '<', 15);
        })->orWhereHas('leader', function (Builder $query) {
            $query->where('age', '>', 30);
        });
    })
        ->distinct()
        ->pluck('led_by');

    expect($locations->count())->toBe(2);
    expect($locations[0])->toBe('CerseiLannister');
    expect($locations[1])->toBe('SansaStark');
});

test('whereDoesntHave', function () {
    $characters = Character::whereDoesntHave('leads', function (Builder $query) {
        $query->where('name', 'Astapor');
    })->get();

    $daenarys = $characters->first(function (Character $character) {
        return $character->id === 'DaenerysTargaryen';
    });
    expect($characters->count())->toBe(42);
    expect($daenarys)->toBeNull();
});

test('orWhereDoesntHave', function () {
    $houses = House::where(function (Builder $query) {
        $query->whereDoesntHave('head', function (Builder $query) {
            $query->where('age', '<', 20);
        })
            ->orWhereDoesntHave('head', function (Builder $query) {
                $query->whereNull('age');
            });
    })->get();

    expect($houses[0]->name)->toBe('Stark');
    expect($houses[1]->name)->toBe('Targaryen');
});



test('whereRelation', function () {
    $locations = Location::whereRelation('leader', 'age', '<', 30)
        ->distinct()
        ->pluck('led_by');

    expect($locations->count())->toBe(2);
    expect($locations[0])->toBe('DaenerysTargaryen');
    expect($locations[1])->toBe('SansaStark');
});

test('orWhereRelation', function () {
    $locations = Location::where(function (Builder $query) {
        $query->whereRelation('leader', 'age', '<', 15)
        ->orWhereRelation('leader', 'age', '>', 30);
    })
        ->distinct()
        ->pluck('led_by');

    expect($locations->count())->toBe(2);
    expect($locations[0])->toBe('CerseiLannister');
    expect($locations[1])->toBe('SansaStark');
});

test('whereDoesntHaveRelation', function () {
    $characters = Character::whereDoesntHaveRelation('leads', 'name', 'Astapor')->get();

    $daenarys = $characters->first(function (Character $character) {
        return $character->id === 'DaenerysTargaryen';
    });

    expect($characters->count())->toBe(42);
    expect($daenarys)->toBeNull();
});

test('orWhereDoesntHaveRelation', function () {
    $houses = House::where(function (Builder $query) {
        $query->whereDoesntHaveRelation('head', 'age', '<', 20)
            ->orWhereDoesntHaveRelation('head', 'age', null);
    })->get();

    expect($houses[0]->name)->toBe('Stark');
    expect($houses[1]->name)->toBe('Targaryen');
});

test('withCount', function () {
    $characters = Character::withCount('leads')
        ->where('leads_count', '>', 0)
        ->get();

    expect(count($characters))->toEqual(3);
});

test('withExists', function () {
    $characters = Character::withExists('leads')
        ->get();

    expect(count($characters))->toEqual(43);
    expect($characters->where('leads_exists', true)->count())->toEqual(3);
});
