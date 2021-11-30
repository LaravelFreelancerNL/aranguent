<?php

namespace Tests\Query;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use LaravelFreelancerNL\Aranguent\Query\Builder;
use LaravelFreelancerNL\Aranguent\Query\Grammar;
use LaravelFreelancerNL\Aranguent\Query\Processor;
use LaravelFreelancerNL\FluentAQL\QueryBuilder as FluentAQL;
use Mockery as m;
use Tests\Setup\Database\Seeds\CharactersSeeder;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../Setup/Database/Migrations');

        Artisan::call('db:seed', ['--class' => CharactersSeeder::class]);
    }

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testInsertGetId()
    {
        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('execute')->once()->with(FluentAQL::class)->andReturn(1);
        $result = $builder->from('users')->insertGetId(['email' => 'foo']);
        $this->assertEquals(1, $result);
    }

    public function testInsertOrIgnoreInsertsData()
    {
        $characterData = [
            "_key" => "LyannaStark",
            "name" => "Lyanna",
            "surname" => "Stark",
            "alive" => false,
            "age" => 25,
            "residence_id" => "winterfell"
        ];

        DB::table('characters')->insertOrIgnore($characterData);

        $result = DB::table('characters')
            ->where("name", "==", "Lyanna")
            ->count();

        $this->assertSame(1, $result);
    }

    public function testInsertOrIgnoreDoesntErrorOnDuplicates()
    {
        $characterData = [
            "_key" => "LyannaStark",
            "name" => "Lyanna",
            "surname" => "Stark",
            "alive" => false,
            "age" => 25,
            "residence_id" => "winterfell"
        ];
        DB::table('characters')->insert($characterData);

        DB::table('characters')->insertOrIgnore($characterData);

        $result = DB::table('characters')
            ->where("name", "==", "Lyanna")
            ->count();

        $this->assertSame(1, $result);
    }


    public function testBasicSelect()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users');
        $this->assertSame('FOR userDoc IN users RETURN userDoc', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select(['name', 'email'])->from('users');
        $this->assertSame('FOR userDoc IN users RETURN {"name":userDoc.name,"email":userDoc.email}', $builder->toSql());
    }

    public function testBasicSelectWithGetColumns()
    {
        $builder = $this->getBuilder();
        $builder->getProcessor()->shouldReceive('processSelect');
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(
            function ($aqb) {
                $this->assertSame('FOR userDoc IN users RETURN userDoc', $aqb->toAql());
            }
        );
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(
            function ($aqb) {
                $this->assertSame(
                    'FOR userDoc IN users RETURN {"name":userDoc.name,"email":userDoc.email}',
                    $aqb->toAql()
                );
            }
        );
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(
            function ($aqb) {
                $this->assertSame('FOR userDoc IN users RETURN userDoc.name', $aqb->toAql());
            }
        );

        $builder->from('users')->get();
        $this->assertNull($builder->columns);

        $builder->from('users')->get(['name', 'email']);
        $this->assertNull($builder->columns);

        $builder->from('users')->get('name');
        $this->assertNull($builder->columns);

        $this->assertSame('FOR userDoc IN users', $builder->toSql());
        $this->assertNull($builder->columns);
    }

    public function testBasicSelectWithGetOneColumn()
    {
        $builder = $this->getBuilder();
        $builder->getProcessor()->shouldReceive('processSelect');
        $builder->getConnection()->shouldReceive('select')->once()->andReturnUsing(
            function ($aqb) {
                $this->assertSame('FOR userDoc IN users RETURN userDoc.name', $aqb->toAql());
            }
        );

        $builder->from('users')->get('name');
        $this->assertNull($builder->columns);
    }

    public function testOrderBys()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->orderBy('email')->orderBy('age', 'desc');
        $this->assertSame(
            'FOR userDoc IN users SORT userDoc.email asc, userDoc.age desc RETURN userDoc',
            $builder->toSql()
        );
    }

    public function testOrderByRandom()
    {
        $results = DB::table('characters')
            ->inRandomOrder()
            ->toSql();

        $this->assertEquals('FOR characterDoc IN characters SORT RAND()', $results);
    }

    public function testLimitsAndOffsets()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->offset(5)->limit(10);
        $this->assertSame('FOR userDoc IN users LIMIT 5, 10 RETURN userDoc', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->skip(5)->take(10);
        $this->assertSame('FOR userDoc IN users LIMIT 5, 10 RETURN userDoc', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->skip(0)->take(0);
        $this->assertSame('FOR userDoc IN users LIMIT 0, 0 RETURN userDoc', $builder->toSql());

        $builder = $this->getBuilder();
        $builder->select('*')->from('users')->skip(-5)->take(-10);
        $this->assertSame('FOR userDoc IN users RETURN userDoc', $builder->toSql());
    }

    public function testUpdateMethod()
    {
        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('update')->once()->with(FluentAQL::class)->andReturn(1);
        $result = $builder->from('users')->where('userDoc._id', '=', 1)->update(['email' => 'foo', 'name' => 'bar']);
        $this->assertEquals(1, $result);
    }

    public function testDeleteMethod()
    {
        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('delete')->once()->with(FluentAQL::class)->andReturn(1);
        $result = $builder->from('users')->where('userDoc.email', '=', 'foo')->delete();
        $this->assertEquals(1, $result);

        $builder = $this->getBuilder();
        $builder->getConnection()->shouldReceive('delete')->once()->with(FluentAQL::class)->andReturn(1);
        $result = $builder->from('users')->delete(1);
        $this->assertEquals(1, $result);
    }

    public function testFirstMethod()
    {
        $result = \DB::table('characters')->where('characterDoc.id', '=', 'NedStark')->first();

        $this->assertSame('NedStark', $result->id);
    }

    public function testAggregates()
    {
        $results = DB::table('characters')->count();
        $this->assertEquals(43, $results);
    }

    public function testPaginate()
    {
        $result = DB::table('characters')->paginate(15)->toArray();
        $this->assertEquals(43, $result['total']);
        $this->assertEquals(15, count($result['data']));
    }

    public function testPluck()
    {
        $results = DB::table('characters')->pluck('name', 'id');

        $this->assertEquals(43, $results->count());
        $this->assertEquals('Ned', $results['NedStark']);
    }

    /**
     * @return m\MockInterface
     */
    protected function getMockQueryBuilder()
    {
        return m::mock(
            Builder::class,
            [
                m::mock(ConnectionInterface::class),
                new Grammar(),
                m::mock(Processor::class),
            ]
        )->makePartial();
    }
}
