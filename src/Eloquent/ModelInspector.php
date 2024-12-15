<?php

namespace LaravelFreelancerNL\Aranguent\Eloquent;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelInspector as IlluminateModelInspector;
use Illuminate\Support\Collection;
use LaravelFreelancerNL\Aranguent\Connection;

use function Illuminate\Support\enum_value;

class ModelInspector extends IlluminateModelInspector
{
    /**
     * The methods that can be called in a model to indicate a relation.
     *
     * @var array<int, string>
     */
    protected $relationMethods = [
        'hasMany',
        'hasManyThrough',
        'hasOneThrough',
        'belongsToMany',
        'hasOne',
        'belongsTo',
        'morphOne',
        'morphTo',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    /**
     * Extract model details for the given model.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>|string  $model
     * @param  string|null  $connection
     * @return array{"class": class-string<\Illuminate\Database\Eloquent\Model>, database: string, table: string, policy: class-string|null, attributes: Collection, relations: Collection, events: Collection, observers: Collection, collection: class-string<\Illuminate\Database\Eloquent\Collection<\Illuminate\Database\Eloquent\Model>>, builder: class-string<\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>>}
     *
     * @throws BindingResolutionException
     */
    public function inspect($model, $connection = null)
    {
        $class = $this->qualifyModel($model);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->app->make($class);

        if ($connection !== null) {
            $model->setConnection($connection);
        }


        /* @phpstan-ignore-next-line  */
        return [
            'class' => get_class($model),
            'database' => $model->getConnection()->getName() ?? '',
            'table' => $model->getConnection()->getTablePrefix() . $model->getTable(),
            'policy' => $this->getPolicy($model) ?? '',
            'attributes' => $this->getAttributes($model),
            'relations' => $this->getRelations($model),
            'events' => $this->getEvents($model),
            'observers' => $this->getObservers($model),
            'collection' => $this->getCollectedBy($model),
            'builder' => $this->getBuilder($model),
        ];
    }

    /**
     * Get the column attributes for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return Collection<int, array<string, mixed>>
     */
    protected function getAttributes($model)
    {
        $connection = $model->getConnection();
        assert($connection instanceof Connection);

        $schema = $connection->getSchemaBuilder();
        $table = $model->getTable();
        $tableData = $schema->getTable($table);
        $columns = $schema->getColumns($table);
        $indexes = $schema->getIndexes($table);

        $columns = $this->addSystemAttributes($columns, $tableData);

        /* @phpstan-ignore-next-line  */
        return collect($columns)
            ->map(fn($column) => [
                'name' => $column['name'],
                'type' => $column['type'],
                'increments' => $column['auto_increment'] ?? null,
                'nullable' => $column['nullable'] ?? null,
                'default' => $this->getColumnDefault($column, $model) ?? null,
                'unique' => $this->columnIsUnique($column['name'], $indexes),
                'fillable' => $model->isFillable($column['name']),
                'computed' => $this->columnIsComputed($column['name'], $tableData),
                'hidden' => $this->attributeIsHidden($column['name'], $model),
                'appended' => null,
                'cast' => $this->getCastType($column['name'], $model),
            ])
            ->merge($this->getVirtualAttributes($model, $columns));
    }

    /**
     * Get the default value for the given column.
     *
     * @param  array<string, mixed>  $column
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed|null
     */
    protected function getColumnDefault($column, $model)
    {
        $attributeDefault = $model->getAttributes()[$column['name']] ?? null;

        return enum_value($attributeDefault, $column['default'] ?? null);
    }

    /**
     * Determine if the given attribute is unique.
     *
     * @param  string  $column
     * @param  mixed[]  $indexes
     * @return bool
     */
    protected function columnIsUnique($column, $indexes)
    {
        return collect($indexes)->contains(
            fn($index) => count($index['fields']) === 1 && $index['fields'][0] === $column && $index['unique'],
        );
    }

    /**
     * @param string $name
     * @param array<string, mixed> $tableData
     * @return bool
     */
    protected function columnIsComputed($name, $tableData)
    {
        $computedValues = (new Collection($tableData['computedValues']))->pluck('name')->toArray();

        return in_array($name, $computedValues);
    }

    /**
     * @param mixed[] $columns
     * @param mixed[] $tableData
     * @return mixed[]
     */
    protected function addSystemAttributes(array $columns, $tableData)
    {
        // edges add _from, _to
        if ($tableData['type'] === 3) {
            array_unshift(
                $columns,
                [
                    'name' => '_to',
                    'type' => 'string',
                    'nullable' => false,
                ],
            );
            array_unshift(
                $columns,
                [
                    'name' => '_from',
                    'type' => 'string',
                    'nullable' => false,
                ],
            );
        }

        // Prepend id,
        array_unshift(
            $columns,
            [
                'name' => 'id',
                'type' => $tableData['keyOptions']->type,
                'nullable' => false,
                'allowUserKeys' => $tableData['keyOptions']->allowUserKeys,
                'unique' => true,
            ],
        );

        return $columns;
    }
}
