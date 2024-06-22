<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Eloquent;

use Illuminate\Database\Eloquent\Builder as IlluminateEloquentBuilder;
use Illuminate\Support\Arr;
use LaravelFreelancerNL\Aranguent\Eloquent\Concerns\QueriesAranguentRelationships;
use LaravelFreelancerNL\Aranguent\Exceptions\UniqueConstraintViolationException;
use LaravelFreelancerNL\Aranguent\Query\Builder as QueryBuilder;

class Builder extends IlluminateEloquentBuilder
{
    use QueriesAranguentRelationships;

    /**
     * The base query builder instance.
     *
     * @var QueryBuilder
     */
    protected $query;

    /**
     * Attempt to create the record. If a unique constraint violation occurs, attempt to find the matching record.
     *
     * @param  mixed[]  $attributes
     * @param  mixed[]  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function createOrFirst(array $attributes = [], array $values = [])
    {
        try {
            return $this->withSavepointIfNeeded(fn() => $this->create(array_merge($attributes, $values)));
        } catch (UniqueConstraintViolationException $e) {
            ray($e);
            return $this->useWritePdo()->where($attributes)->first() ?? throw $e;
        }
    }

    /**
     * Insert a record in the database.
     *
     *
     * @param array<mixed> $values
     * @return bool
     */
    public function insert(array $values)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return true;
        }

        if (Arr::isAssoc($values)) {
            $values = [$values];
        }
        if (!Arr::isAssoc($values)) {
            // Here, we will sort the insert keys for every record so that each insert is
            // in the same order for the record. We need to make sure this is the case
            // so there are not any errors or problems when inserting these records.
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        //Set timestamps
        foreach ($values as $key => $value) {
            $values[$key] = $this->updateTimestamps($value);
        }

        return $this->toBase()->insert($values);
    }

    /**
     * Add the "updated at" column to an array of values.
     *
     * @param array<string, string> $values
     * @return array<string, string>
     */
    protected function updateTimestamps(array $values)
    {
        if (
            !$this->model->usesTimestamps() ||
            is_null($this->model->getUpdatedAtColumn()) ||
            is_null($this->model->getCreatedAtColumn())
        ) {
            return $values;
        }

        $timestamp = $this->model->freshTimestampString();
        $updatedAtColumn = $this->model->getUpdatedAtColumn();

        $timestamps = [];
        $timestamps[$updatedAtColumn] = $timestamp;

        $createdAtColumn = $this->model->getCreatedAtColumn();
        if (!isset($values[$createdAtColumn]) && !isset($this->model->$createdAtColumn)) {
            $timestamps[$createdAtColumn] = $timestamp;
        }

        $values = array_merge(
            $timestamps,
            $values,
        );

        return $values;
    }

    /**
     * Add the "updated at" column to an array of values.
     *
     * @param array<string> $values
     * @return array<string>
     */
    protected function addUpdatedAtColumn(array $values): array
    {
        if (
            !$this->model->usesTimestamps() ||
            is_null($this->model->getUpdatedAtColumn())
        ) {
            return $values;
        }

        $column = $this->model->getUpdatedAtColumn();

        $values = array_merge(
            [$column => $this->model->freshTimestampString()],
            $values,
        );

        return $values;
    }

    /**
     * Get the underlying query builder instance.
     *
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }
}
