<?php

use LaravelFreelancerNL\Aranguent\Testing\DatabaseTransactions;
use Tests\Setup\Models\Character;
use Tests\Setup\Models\Tag;
use Tests\TestCase;

uses(
    TestCase::class,
    DatabaseTransactions::class
);

test('retrieve relation', function () {
    $character = Character::find('SandorClegane');

    $tags = $character->tags;

    expect(count($tags))->toEqual(4);
    expect($tags[0])->toBeInstanceOf(Tag::class);
});

test('inverse relation', function () {
    $tag = Tag::find('A');

    $characters = $tag->characters;
    $locations = $tag->locations;

    expect(count($characters))->toEqual(1);
    expect(count($locations))->toEqual(1);
});

test('attach', function () {
    $character = Character::find('Varys');


    $character->tags()->attach(['B', 'E', 'J', 'N', 'O']);
    $character->save();

    $character->fresh();
    $tags = $character->tags;

    expect(count($tags))->toEqual(5);
    expect($character->tags[0]->id)->toEqual('B');
});

test('detach', function () {
    $character = Character::find('SandorClegane');

    $character->tags()->detach(['F', 'K']);
    $character->save();

    $reloadedCharacter = Character::find('SandorClegane');

    expect(count($reloadedCharacter->tags))->toEqual(2);
    expect($reloadedCharacter->tags[0]->id)->toEqual('A');
    expect($reloadedCharacter->tags[1]->id)->toEqual('P');
});

test('sync', function () {
    $character = Character::find('SandorClegane');

    $character->tags()->sync(['C', 'J']);
    $character->save();

    $reloadedCharacter = Character::find('SandorClegane');

    expect(count($reloadedCharacter->tags))->toEqual(2);
    expect($reloadedCharacter->tags[0]->id)->toEqual('C');
    expect($reloadedCharacter->tags[1]->id)->toEqual('J');
});
