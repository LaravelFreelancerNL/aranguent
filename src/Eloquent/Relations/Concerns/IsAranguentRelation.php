<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Eloquent\Relations\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

trait IsAranguentRelation
{
    /**
     * Get all of the primary keys for an array of models.
     *
     * @param  array<mixed>  $models
     * @param  string|null  $key
     * @return array<int, int|string>
     */
    protected function getKeys(array $models, $key = null)
    {
        // The original function orders the results associatively by value which means the keys reorder too.
        // However, a list of keys with unordered numeric keys will be recognized as an object down the line
        // for json casting while we need a list of keys.

        $keys = collect($models)->map(function ($value) use ($key) {
            return $key ? $value->getAttribute($key) : $value->getKey();
        })->values()->unique(null, true)->all();

        sort($keys);

        return $keys;
    }

    /**
     * Add the constraints for a relationship count query.
     *
     * @return Builder
     */
    public function getRelationExistenceCountQuery(Builder $query, Builder $parentQuery)
    {
        return $this->getRelationExistenceQuery(
            $query,
            $parentQuery,
            new Expression('*'),
        );
    }
}
