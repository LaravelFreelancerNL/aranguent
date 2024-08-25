<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing\Concerns;

use Illuminate\Support\Facades\DB;

trait InteractsWithDatabase
{
    /**
     * Cast a JSON string to a database compatible type.
     * Supported for backwards compatibility in existing projects.
     * No cast is necessary as json is a first class citizen in ArangoDB.
     *
     * @param  array<mixed>|object|string  $value
     * @return \Illuminate\Contracts\Database\Query\Expression
     */
    public function castAsJson($value)
    {
        return DB::raw($value);
    }
}
