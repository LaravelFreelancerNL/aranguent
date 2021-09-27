<?php

namespace Tests\Eloquent;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use LaravelFreelancerNL\Aranguent\Eloquent\Model;
use Mockery as M;
use Tests\setup\Models\Character;
use Tests\Setup\Models\Location;
use Tests\TestCase;

class BelongsToTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now());

        Artisan::call('db:seed', ['--class' => \Tests\Setup\Database\Seeds\CharactersSeeder::class]);
        Artisan::call('db:seed', ['--class' => \Tests\Setup\Database\Seeds\LocationsSeeder::class]);
        Artisan::call('db:seed', ['--class' => \Tests\Setup\Database\Seeds\ChildrenSeeder::class]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow(null);
        Carbon::resetToStringFormat();
        Model::unsetEventDispatcher();
        M::close();
    }

    public function testRetrieveRelation()
    {
        $parent = Character::find('characters/NedStark');
        $children = $parent->children;

        $this->assertInstanceOf(Character::class, $children[0]);

        $this->assertTrue(true);
    }

    public function testAlternativeRelationshipNameAndKey()
    {
        $location = Location::find('locations/winterfell');
        $character = $location->leader;

        $this->assertEquals('SansaStark', $character->_key);
        $this->assertEquals($location->led_by, $character->_id);
        $this->assertInstanceOf(Character::class, $character);
    }

    public function testAssociate()
    {
        $character = Character::find('characters/TheonGreyjoy');

        $location = new Location(
            [
                "_key" => "pyke",
                "name" => "Pyke",
                "coordinate" => [55.8833342, -6.1388807]
            ]
        );

        $location->leader()->associate($character);
        $location->save();

        $character = Character::find('characters/TheonGreyjoy');

        $location = $character->leads;

        $this->assertEquals('pyke', $location->_key);
        $this->assertEquals($location->led_by, $character->_id);
        $this->assertInstanceOf(Location::class, $location);
    }

    public function testDissociate()
    {
        $character = Character::find('characters/NedStark');
        $this->assertEquals($character->residence_id, 'locations/winterfell');

        $character->residence()->dissociate();
        $character->save();

        $character = Character::find('characters/NedStark');
        $this->assertNull($character->residence_id);
    }

    public function testWith(): void
    {
        $location = Location::with('leader')->find("locations/winterfell");

        $this->assertInstanceOf(Character::class, $location->leader);
        $this->assertEquals('SansaStark', $location->leader->_key);
    }
}
