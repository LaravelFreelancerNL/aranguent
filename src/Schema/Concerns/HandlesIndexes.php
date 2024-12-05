<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Schema\Concerns;

use ArangoClient\Exceptions\ArangoException;

trait HandlesIndexes
{
    /**
     * Determine if the given table has a given index.
     *
     * @param  string  $table
     * @param  string|array<string>  $index
     * @param  string|null  $type
     * @return bool
     */
    public function hasIndex($table, $index, $type = null, array $options = [])
    {
        $name = $index;

        if ($type === null) {
            $type = 'persistent';
        }

        if (is_array($index)) {
            $name = $this->createIndexName($type, $index, $options, $table);
        }

        return !!$this->schemaManager->getIndexByName($table, $name);
    }

    /**
     * @param string $id
     * @return array<mixed>
     */
    public function getIndex(string $id)
    {
        return (array) $this->schemaManager->getIndex($id);
    }

    /**
     * @param string $table
     * @return mixed[]
     * @throws ArangoException
     */
    public function getIndexes($table)
    {
        return $this->mapResultsToArray(
            $this->schemaManager->getIndexes($table),
        );
    }
}
