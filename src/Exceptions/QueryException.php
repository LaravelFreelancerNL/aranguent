<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Exceptions;

use Illuminate\Database\QueryException as IlluminateQueryException;
use Throwable;

class QueryException extends IlluminateQueryException
{
    /**
     * Create a new query exception instance.
     *
     * @param  string  $connectionName
     * @param  string  $sql
     * @param  mixed[]  $bindings
     * @param  \Throwable  $previous
     * @return string
     */
    protected function formatMessage($connectionName, $sql, $bindings, Throwable $previous)
    {
        return $previous->getMessage()
            . ' (Connection: ' . $connectionName
            . ',AQL: ' . $sql
            . ' - Bindings: ' . var_export($bindings, true)
            . ')';
    }
}
