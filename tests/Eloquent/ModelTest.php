<?php

use LaravelFreelancerNL\Aranguent\Testing\DatabaseTransactions;
use TestSetup\Models\Character;
use TestSetup\Models\User;
use LaravelFreelancerNL\Aranguent\Exceptions\QueryException;

uses(
    DatabaseTransactions::class,
);

test('update model', function () {
    $character = Character::first();
    $initialAge = $character->age;

    $character->update(['age' => ($character->age + 1)]);

    $fresh = $character->fresh();

    expect($fresh->age)->toBe(($initialAge + 1));
});

test('updateOrCreate', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    $user1 = User::updateOrCreate($userData);

    $user2 = User::where("email", "d.the.tall@hedgeknight.com")->first();

    expect($user1->_id)->toBe($user2->_id);
});

test('updateOrCreate runs twice', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];
    $user1 = User::updateOrCreate($userData);

    $user2 = User::updateOrCreate($userData);

    expect($user1->_id)->toBe($user2->_id);
});

test('updateOrCreate throws error on unique key if data is different', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];
    $user1 = User::updateOrCreate($userData);

    $userData = [
        "username" => "Duncan the Tall",
        "email" => "d.the.tall@hedgeknight.com",
    ];
    $user2 = User::updateOrCreate($userData);

    expect($user1->_id)->toBe($user2->_id);
})->throws(QueryException::class);


test('upsert', function () {
    Character::upsert(
        [
            [
                'id' => 'NedStark',
                'name' => 'Ned',
                'surname' => 'Stark',
                'alive' => false,
                'age' => 41,
                'residence_id' => 'winterfell',
            ],
            [
                'id' => 'JaimeLannister',
                'name' => 'Jaime',
                'surname' => 'Lannister',
                'alive' => false,
                'age' => 36,
                'residence_id' => 'the-red-keep',
            ],
        ],
        ['name', 'surname'],
        ['alive'],
    );

    $ned = Character::find('NedStark');
    $jaime = Character::find('JaimeLannister');

    expect($ned->alive)->toBeFalse();
    expect($jaime->alive)->toBeFalse();
});

test('upsert runs twice', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    User::upsert([$userData], ['email'], ['username']);

    $userData = [
        "username" => "Duncan the Tall",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    $result = User::upsert([$userData], ['email'], ['username']);

    $user = User::where("email", "d.the.tall@hedgeknight.com")->first();

    expect($result)->toBe(1);
    expect($user->username)->toBe("Duncan the Tall");
});


test('delete model', function () {
    $character = Character::first();

    $character->delete();

    $deletedCharacter = Character::first();

    $this->assertNotEquals($character->id, $deletedCharacter->id);
});

test('destroy model', function () {
    $id = 'NedStark';
    Character::destroy($id);

    $this->assertDatabaseMissing('characters', ['id' => $id]);
});

test('truncate model', function () {
    Character::truncate();

    $this->assertDatabaseCount('characters', 0);
});

test('count', function () {
    $result = Character::count();
    expect($result)->toEqual(43);
});

test('max', function () {
    $result = Character::max('age');
    expect($result)->toEqual(49);
});

test('min', function () {
    $result = Character::min('age');
    expect($result)->toEqual(10);
});

test('average', function () {
    $result = Character::average('age');
    expect($result)->toEqual(25.6);
});

test('sum', function () {
    $result = Character::sum('age');
    expect($result)->toEqual(384);
});

test('get id', function () {
    $ned = Character::first();
    expect($ned->id)->toEqual('NedStark');
});

test('set underscore id', function () {
    $ned = Character::first();
    $ned->_id = 'characters/NedStarkIsDead';

    expect($ned->_id)->toEqual('characters/NedStarkIsDead');
    expect($ned->id)->toEqual('NedStarkIsDead');
});

test('set id', function () {
    $ned = Character::first();
    $ned->id = 'NedStarkIsDead';

    expect($ned->id)->toEqual('NedStarkIsDead');
    expect($ned->_id)->toEqual('characters/NedStarkIsDead');
});

test('firstOrCreate', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    $user = User::firstOrCreate($userData);

    $result = DB::table('users')
        ->where('email', 'd.the.tall@hedgeknight.com')
        ->first();

    expect($result->_id)->toBe($user->_id);
});

test('firstOrCreate runs twice without error', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    $user1 = User::firstOrCreate($userData);
    $user2 = User::firstOrCreate($userData);

    expect($user1->_id)->toBe($user2->_id);
});

test('firstOrCreate throws error if data is different', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    $user1 = User::firstOrCreate($userData);

    $userData = [
        "username" => "Duncan the Tall",
        "email" => "d.the.tall@hedgeknight.com",
    ];
    $user2 = User::firstOrCreate($userData);

    expect($user1->_id)->toBe($user2->_id);
})->throws(QueryException::class);


test('createOrFirst', function () {
    $userData = [
        "username" => "Dunk",
        "email" => "d.the.tall@hedgeknight.com",
    ];

    $user = User::createOrFirst($userData);

    $result = DB::table('users')
        ->where('email', 'd.the.tall@hedgeknight.com')
        ->first();

    expect($result->_id)->toBe($user->_id);
});
