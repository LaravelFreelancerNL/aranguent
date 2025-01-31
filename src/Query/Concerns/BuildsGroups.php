<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Query\Concerns;

use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Contracts\Database\Query\ConditionExpression;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;

trait BuildsGroups
{
    /**
     * @var array<string>|null
     */
    public $groupVariables = null;

    /**
     * Add a "group by" clause to the query.
     *
     * @param array<mixed>|Expression|string ...$groups
     * @return $this
     */
    public function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Arr::wrap($group),
            );
        }

        return $this;
    }

    /**
     * Add a raw groupBy clause to the query.
     *
     * @param  string  $aql
     * @param  array<mixed>  $bindings
     * @return $this
     */
    public function groupByRaw($aql, array $bindings = [])
    {
        $this->groups[] = new Expression($aql);

        if (!empty($bindings)) {
            $this->addBinding($bindings, 'groupBy');
        }

        return $this;
    }

    public function cleanGroupVariables(): void
    {
        $this->tableAliases = array_diff($this->tableAliases, $this->groupVariables ?? []);
        $this->groupVariables = null;
    }

    /**
     * @param ConditionExpression|string $column
     * @param null|string $operator
     * @param $value
     * @param $boolean
     * @return $this|\LaravelFreelancerNL\Aranguent\Query\Builder
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Basic';

        if ($column instanceof ConditionExpression) {
            $type = 'Expression';

            $this->havings[] = compact('type', 'column', 'boolean');

            return $this;
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value,
            $operator,
            func_num_args() === 2,
        );

        /** @phpstan-ignore-next-line  */
        if ($column instanceof Closure && is_null($operator)) {
            return $this->havingNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        if ($this->isBitwiseOperator($operator)) {
            $type = 'Bitwise';
        }

        if (!$value instanceof Expression) {
            $this->addBinding($this->flattenValue($value), 'having');
            $value = '@' . array_key_last($this->getBindings());
        }

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param  string  $sql
     * @param  array<mixed>  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'Raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        if (!empty($bindings)) {
            $this->addBinding($bindings, 'having');
        }

        return $this;
    }

    /**
     * Create a new query instance for nested where condition.
     *
     * @param array<mixed> $aliases
     * @return Builder
     */
    public function forNestedWhere($aliases = [])
    {
        $query = $this->newQuery();
        foreach ($aliases as $alias) {
            $query->groups[] = $alias;

        }
        return $query->from((string) $this->grammar->getValue($this->from));
    }

    /**
     * Add a nested having statement to the query.
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    public function havingNested(Closure $callback, $boolean = 'and')
    {
        $callback($query = $this->forNestedWhere($this->groups ?? []));

        return $this->addNestedHavingQuery($query, $boolean);
    }

    /**
     * Add another query builder as a nested having to the query builder.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $boolean
     * @return $this
     */
    public function addNestedHavingQuery($query, $boolean = 'and')
    {
        if (count($query->havings ?? [])) {
            $type = 'Nested';

            $this->havings[] = compact('type', 'query', 'boolean');

            $this->mergeBindings($query);
        }

        return $this;
    }


    /**
     * Add a "having between " clause to the query.
     *
     * @param  string  $column
     * @param  iterable<mixed>  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function havingBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        if ($values instanceof CarbonPeriod) {
            $values = [$values->start, $values->end];
        }

        assert(is_array($values));

        $bindings = array_slice($this->cleanBindings(Arr::flatten($values)), 0, 2);
        $this->addBinding($bindings[0], 'having');
        $values[0] = '@' . array_key_last($this->getBindings());

        $this->addBinding($bindings[1], 'having');
        $values[1] = '@' . array_key_last($this->getBindings());

        $this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');

        return $this;
    }
}
