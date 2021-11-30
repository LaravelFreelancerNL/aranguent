<?php

namespace Tests\Eloquent;

use Illuminate\Support\Carbon;
use LaravelFreelancerNL\Aranguent\Eloquent\Model;
use Mockery as M;
use Tests\Setup\Models\Character;
use Tests\TestCase;

class ModelTest extends TestCase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../Setup/Database/Migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::now());

        Character::insert(
            [
                [
                    '_key'    => 'NedStark',
                    'name'    => 'Ned',
                    'surname' => 'Stark',
                    'alive'   => false,
                    'age'     => 41,
                    'traits'  => ['A', 'H', 'C', 'N', 'P'],
                ],
                [
                    '_key'    => 'RobertBaratheon',
                    'name'    => 'Robert',
                    'surname' => 'Baratheon',
                    'alive'   => false,
                    'age'     => null,
                    'traits'  => ['A', 'H', 'C'],
                ],
            ]
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Carbon::setTestNow(null);
        Carbon::resetToStringFormat();

        Model::unsetEventDispatcher();

        M::close();
    }

    public function testCreateAranguentModel()
    {
        $this->artisan(
            'aranguent:model',
            [
                'name'    => 'Aranguent',
                '--force' => '',
            ]
        )->run();

        $file = __DIR__ . '/../../vendor/orchestra/testbench-core/laravel/app/Models/Aranguent.php';

        //assert file exists
        $this->assertFileExists($file);

        //assert file refers to Aranguent Base Model
        $content = file_get_contents($file);
        $this->assertStringContainsString('use LaravelFreelancerNL\Aranguent\Eloquent\Model;', $content);
    }

    public function testUpdateModel()
    {
        $character = Character::first();
        $initialAge = $character->age;

        $character->update(['age' => ($character->age + 1)]);

        $fresh = $character->fresh();

        $this->assertSame(($initialAge + 1), $fresh->age);
    }

    public function testUpdateOrCreate()
    {
        $character = Character::first();
        $initialAge = $character->age;
        $newAge = ($initialAge + 1);

        $character->updateOrCreate(['age' => $initialAge], ['age' => $newAge]);

        $fresh = $character->fresh();

        $this->assertSame($newAge, $fresh->age);
    }

    public function testUpsert()
    {
        $this->skipTestOnArangoVersionsBefore('3.7');

        Character::upsert(
            [
                [
                   "id" => "NedStark",
                   "name" => "Ned",
                   "surname" => "Stark",
                   "alive" => false,
                   "age" => 41,
                   "residence_id" => "winterfell"
                ],
                [
                   "id" => "JaimeLannister",
                   "name" => "Jaime",
                   "surname" => "Lannister",
                   "alive" => false,
                   "age" => 36,
                   "residence_id" => "the-red-keep"
                ],
            ],
            ['name', 'surname'],
            ['alive']
        );

        $ned = Character::find('NedStark');
        $jaime = Character::find('JaimeLannister');

        $this->assertFalse($ned->alive);
        $this->assertFalse($jaime->alive);
    }

    public function testDeleteModel()
    {
        $character = Character::first();

        $character->delete();

        $deletedCharacter = Character::first();

        $this->assertNotEquals($character->id, $deletedCharacter->id);
    }

    public function testDestroyModel()
    {
        $id = 'NedStark';
        Character::destroy($id);

        $this->assertDatabaseMissing('characters', ['id' => $id]);
    }


    public function testTruncateModel()
    {
        Character::truncate();

        $this->assertDatabaseCount('characters', 0);
    }

    public function testCount()
    {
        $result = Character::count();
        $this->assertEquals(2, $result);
    }

    public function testMax()
    {
        $result = Character::max('age');
        $this->assertEquals(41, $result);
    }

    public function testMin()
    {
        $result = Character::min('age');
        $this->assertEquals(41, $result);
    }

    public function testAverage()
    {
        $result = Character::average('age');
        $this->assertEquals(41, $result);
    }

    public function testSum()
    {
        $result = Character::sum('age');
        $this->assertEquals(41, $result);
    }


    public function testGetId()
    {
        $ned = Character::first();
        $this->assertEquals('NedStark', $ned->id);
    }

    public function testSetUnderscoreId()
    {
        $ned = Character::first();
        $ned->_id = 'characters/NedStarkIsDead';

        $this->assertEquals('characters/NedStarkIsDead', $ned->_id);
        $this->assertEquals('NedStarkIsDead', $ned->id);
    }

    public function testSetId()
    {
        $ned = Character::first();
        $ned->id = 'NedStarkIsDead';

        $this->assertEquals('NedStarkIsDead', $ned->id);
        $this->assertEquals('characters/NedStarkIsDead', $ned->_id);
    }
}
