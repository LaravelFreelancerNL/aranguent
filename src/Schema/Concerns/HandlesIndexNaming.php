<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Schema\Concerns;

trait HandlesIndexNaming
{
    /**
     * Create a default index name for the table.
     *
     * @param  string  $type
     */
    public function createIndexName($type, array $columns, array $options = [], string $table = null): string
    {
        $nameParts = [];
        $nameParts[] = $this->prefix . ($table ?? $this->table);
        $nameParts = array_merge($nameParts, $this->getColumnNames($columns));
        $nameParts[] = $type;
        $nameParts = array_merge($nameParts, array_keys($options));
        array_filter($nameParts);

        $index = strtolower(implode('_', $nameParts));
        $index = preg_replace("/\[\*+\]+/", '_array', $index);

        return preg_replace('/[^A-Za-z0-9]+/', '_', $index);
    }

    protected function getColumnNames(array $columns): array
    {
        $names = [];
        foreach ($columns as $column) {
            if (is_array($column) && $column['name'] !== '') {
                $names[] = $column['name'];

                continue;
            }
            $names[] = $column;
        }

        return $names;
    }
}
