<?php

use Illuminate\Database\QueryException as IlluminateQueryException;
use Illuminate\Support\Facades\DB;
use LaravelFreelancerNL\Aranguent\Exceptions\QueryException;

test('bad query throws query exception', function () {
    $this->expectException(QueryException::class);

    DB::execute("this is not AQL", ['testBind' => 'test']);
});

test('query exception extends illuminate', function () {
    $this->expectException(IlluminateQueryException::class);

    DB::execute("this is not AQL", ['testBind' => 'test']);
});

test('query exception has correct message', function () {
    DB::execute('this is not AQL', ['testBind' => 'test']);
})->throws(QueryException::class, 'this is not AQL');

test('query exception without binds', function () {
    expect(fn() => DB::execute("this is not AQL", []))->toThrow(QueryException::class);
});
