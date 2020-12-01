<?php

namespace LaravelFreelancerNL\Aranguent\Query\Concerns;

use Illuminate\Support\Str;
use LaravelFreelancerNL\Aranguent\Query\Builder;

trait HasAliases {

    protected $tableAliases = [];

    protected $columnAliases = [];

    /**
     * @param string $table
     * @param string $alias
     */
    public function registerTableAlias(string $table, string $alias = null): string
    {
        if ($alias == null) {
            $alias = $this->generateTableAlias($table);
        }
        $this->tableAliases[$table] = $alias;

        return $alias;
    }

    public function getTableAlias($table)
    {
        if (isset($this->tableAliases[$table])) {
            return $this->tableAliases[$table];
        }
    }

    /**
     * @param  string  $column
     * @param  string|null  $alias
     * @return bool
     */
    public function registerColumnAlias(string $column, string $alias = null)
    {
        if (preg_match("/\sas\s/i", $column)) {
            [$column, $alias] = $this->extractAlias($column);
        }

        if (isset($alias)) {
            $this->columnAliases[$column] = $alias;
            return true;
        }

        return false;
    }

    /**
     * @param $column
     * @return mixed
     */
    public function getColumnAlias(string $column)
    {
        if (isset($this->columnAliases[$column])) {
            return $this->columnAliases[$column];
        }
    }

    /**
     * Extract table and alias from sql alias notation (entity AS `alias`)
     *
     * @param  string  $entity
     * @return array|false|string[]
     */
    public function extractAlias(string $entity)
    {
        $results = [];
        $results = preg_split( "/\sas\s/i", $entity);
        $results[1] = trim($results[1], '`');

        return $results;
    }

    /**
     * @param $table
     * @param string $postfix
     *
     * @return mixed
     */
    public function generateTableAlias($table, $postfix = 'Doc')
    {
        return Str::singular($table) . $postfix;
    }

    public function replaceTableForAlias($reference): string
    {
        $referenceParts = explode('.', $reference);
        $first = array_shift($referenceParts);
        $alias = $this->getTableAlias($first);
        if ($alias == null) {
            $alias = $first;
        }
        array_unshift($referenceParts, $alias);

        return implode('.', $referenceParts);
    }

    /**
     * @param string  $target
     * @param string  $value
     *
     * @return Builder
     */
    protected function prefixAlias(string $target, string $value): string
    {
        $alias = $this->getTableAlias($target);

        if (Str::startsWith($value, $alias . '.')) {
            return $value;
        }

        return $alias . '.' . $value;
    }

}