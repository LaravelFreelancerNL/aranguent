<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use LaravelFreelancerNL\Aranguent\Eloquent\Model;
use Mockery as M;
use Tests\Setup\Database\Seeds\CharactersSeeder;
use Tests\Setup\Database\Seeds\ChildrenSeeder;
use Tests\Setup\Database\Seeds\LocationsSeeder;
use Tests\setup\Models\Character;
use Tests\TestCase;


beforeEach(function () {
    Carbon::setTestNow(Carbon::now());
});

afterEach(function () {
    Carbon::setTestNow(null);
    Carbon::resetToStringFormat();

    Model::unsetEventDispatcher();

    M::close();
});

test('retrieve relation', function () {
    $parent = Character::find('NedStark');

    $children = $parent->children;

    expect(count($children))->toEqual(5);
    expect($children[0])->toBeInstanceOf(Character::class);
    expect($children[0]->pivot->_from)->toEqual('characters/NedStark');

    expect(true)->toBeTrue();
});

test('inverse relation', function () {
    $child = Character::find('JonSnow');

    $parents = $child->parents;
    expect($parents)->toHaveCount(1);
    expect($parents[0])->toBeInstanceOf(Character::class);
    expect($parents[0]->_id)->toEqual('characters/NedStark');

    expect(true)->toBeTrue();
});

test('attach', function () {
    $child = Character::find('JonSnow');

    $lyannaStark = Character::firstOrCreate(
        [
            "id" => "LyannaStark",
            "name" => "Lyanna",
            "surname" => "Stark",
            "alive" => false,
            "age" => 25,
            "residence_id" => "winterfell"
        ]
    );

    // Reload from DB
    $lyannaStark = Character::find('LyannaStark');

    $child->parents()->attach($lyannaStark);
    $child->save();

    $reloadedChild = Character::find('JonSnow');
    $parents = $child->parents;

    expect($parents[0]->id)->toEqual('NedStark');
    expect($parents[1]->id)->toEqual('LyannaStark');

    $child->parents()->detach($lyannaStark);
    $child->save();
    $lyannaStark->delete();
});

test('detach', function () {
    $child = Character::find('JonSnow');

    $child->parents()->detach('characters/NedStark');
    $child->save();

    $child = $child->fresh();

    expect($child->parents)->toHaveCount(0);
});

test('sync', function () {
    $lyannaStark = Character::firstOrCreate(
        [
            "id" => "LyannaStark",
            "name" => "Lyanna",
            "surname" => "Stark",
            "alive" => false,
            "age" => 25,
            "residence_id" => "winterfell"
        ]
    );
    $rhaegarTargaryen = Character::firstOrCreate(
        [
            "id" => "RhaegarTargaryen",
            "name" => "Rhaegar",
            "surname" => "Targaryen",
            "alive" => false,
            "age" => 25,
            "residence_id" => "dragonstone"
        ]
    );

    $child = Character::find('JonSnow');

    $child->parents()->sync(['characters/LyannaStark', 'characters/RhaegarTargaryen']);
    $child->fresh();

    expect(count($child->parents))->toEqual(2);
    expect($child->parents[0]->_id)->toEqual('characters/LyannaStark');
    expect($child->parents[1]->_id)->toEqual('characters/RhaegarTargaryen');
    expect($child->parents[0]->id)->toEqual('LyannaStark');
    expect($child->parents[1]->id)->toEqual('RhaegarTargaryen');

    $child->parents()->sync('characters/NedStark');
    $rhaegarTargaryen->delete();
    $lyannaStark->delete();
});

// Helpers
function defineDatabaseMigrations()
{
    test()->loadLaravelMigrations();
    test()->loadMigrationsFrom(__DIR__ . '/../Setup/Database/Migrations');

    Artisan::call('db:seed', ['--class' => CharactersSeeder::class]);
    Artisan::call('db:seed', ['--class' => ChildrenSeeder::class]);
    Artisan::call('db:seed', ['--class' => LocationsSeeder::class]);
}
