<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelFreelancerNL\Aranguent\Testing\RefreshDatabase;
use TestSetup\Enums\UserStatus;
use TestSetup\Models\User;

uses(RefreshDatabase::class);

test('Ensure boolean is stored as boolean', function () {
    $user = User::create([
        'email' => fake()->email,
        'is_admin' => false,
    ]);

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    $refreshedUser = $user->refresh();

    expect($user->is_admin)->toBeBool();
    expect($refreshedUser->is_admin)->toBeBool();
    expect($retrievedUser->is_admin)->toBeBool();
});

test('Cast attribute to associative array', function () {
    $profile = [
        'firstName' => fake()->firstName,
        'lastName' => fake()->lastName,
    ];

    $user = User::create([
        'email' => fake()->email,
        'profileAsArray' => $profile,
    ]);

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    $refreshedUser = $user->refresh();

    expect($user->profileAsArray)->toBeArray();
    expect($refreshedUser->profileAsArray)->toBeArray();
    expect($retrievedUser->profileAsArray)->toBeObject();
});

test('Cast attribute to ArrayObject', function () {
    $profile = [
        'firstName' => fake()->firstName,
        'lastName' => fake()->lastName,
    ];

    $user = User::create([
        'email' => fake()->email,
        'profileAsArrayObjectCast' => $profile,
    ]);

    $user->profileAsArrayObjectCast->age = 24;
    $user->save();

    $refreshedUser = $user->refresh();

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    expect($user->profileAsArrayObjectCast)->toBeInstanceOf(ArrayObject::class);
    expect($refreshedUser->profileAsArrayObjectCast)->toBeInstanceOf(ArrayObject::class);
    expect($refreshedUser->profileAsArrayObjectCast->age)->toBe(24);
    expect($retrievedUser->profileAsArrayObjectCast)->toBeObject();
});

test('Cast attribute to object', function () {
    $profile = [
        'firstName' => fake()->firstName,
        'lastName' => fake()->lastName,
    ];

    $user = User::create([
        'email' => fake()->email,
        'profileAsObject' => $profile,
    ]);

    $refreshedUser = $user->refresh();

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    expect($user->profileAsObject)->toBeObject();
    expect($refreshedUser->profileAsObject)->toBeObject();
    expect($retrievedUser->profileAsObject)->toBeObject();
});

test('Cast attribute to string collection', function () {
    $favorites = [];
    for ($i = 0; $i < 10; $i++) {
        $favorites [] = fake()->unique()->url;
    }

    $user = User::create([
        'email' => fake()->email,
        'favoritesCollection' => collect($favorites),
    ]);

    $refreshedUser = $user->refresh();

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    expect($user->favoritesCollection)->toBeInstanceOf(Collection::class);
    expect($refreshedUser->favoritesCollection)->toBeInstanceOf(Collection::class);
    expect($retrievedUser->favoritesCollection)->toBeArray();
    expect($retrievedUser->favoritesCollection[0])->toBeString();
});

test('Cast attribute to collection', function () {
    $favorites = [];
    for ($i = 0; $i < 10; $i++) {
        $favorites [] = [
            'name' => fake()->unique()->company,
            'website' => fake()->unique()->url,
        ];
    }

    $user = User::create([
        'email' => fake()->email,
        'favoritesCollection' => collect($favorites),
    ]);

    $refreshedUser = $user->refresh();

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    expect($user->favoritesCollection)->toBeInstanceOf(Collection::class);
    expect($refreshedUser->favoritesCollection)->toBeInstanceOf(Collection::class);
    expect($retrievedUser->favoritesCollection)->toBeArray();
    expect($retrievedUser->favoritesCollection[0])->toBeObject();
});

test('Cast attribute to AsCollection object', function () {
    $favorites = [];
    for ($i = 0; $i < 10; $i++) {
        $favorites [] = [
            'name' => fake()->unique()->company,
            'website' => fake()->unique()->url,
        ];
    }

    $user = User::create([
        'email' => fake()->email,
        'favoritesAsCollectionCast' => collect($favorites),
    ]);

    $refreshedUser = $user->refresh();

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    expect($user->favoritesAsCollectionCast)->toBeInstanceOf(Collection::class);
    expect($refreshedUser->favoritesAsCollectionCast)->toBeInstanceOf(Collection::class);
    expect($retrievedUser->favoritesAsCollectionCast)->toBeArray();
    expect($retrievedUser->favoritesAsCollectionCast[0])->toBeObject();
});

test('Cast attribute to json', function () {
    $profile = [
        'firstName' => fake()->firstName,
        'lastName' => fake()->lastName,
    ];

    $user = User::create([
        'email' => fake()->email,
        'profileAsJson' => $profile,
    ]);

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    $refreshedUser = $user->refresh();

    expect($user->profileAsJson)->toBeArray();
    expect($refreshedUser->profileAsJson)->toBeArray();
    expect($retrievedUser->profileAsJson)->toBeObject();
});

test('Cast attribute to AsEnumArrayObject', function () {
    $statuses = [
        UserStatus::Registered,
        UserStatus::Verified,
    ];

    $user = User::create([
        'email' => fake()->email,
        'statusesAsEnumArrayObject' => $statuses,
    ]);

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    $refreshedUser = $user->refresh();

    expect($user->statusesAsEnumArrayObject)->toBeInstanceOf(ArrayObject::class);
    expect($refreshedUser->statusesAsEnumArrayObject)->toBeInstanceOf(ArrayObject::class);
    expect($retrievedUser->statusesAsEnumArrayObject)->toBeArray();
});

test('Cast attribute to AsEnumCollection', function () {
    $statuses = [
        UserStatus::Registered,
        UserStatus::Verified,
    ];

    $user = User::create([
        'email' => fake()->email,
        'statusesAsEnumCollection' => $statuses,
    ]);

    $retrievedUser = DB::table('users')->where('email', $user->email)->first();

    $refreshedUser = $user->refresh();

    expect($user->statusesAsEnumCollection)->toBeInstanceOf(Collection::class);
    expect($refreshedUser->statusesAsEnumCollection)->toBeInstanceOf(Collection::class);
    expect($retrievedUser->statusesAsEnumCollection)->toBeArray();
    expect($retrievedUser->statusesAsEnumCollection[0])->toBeString();
});
