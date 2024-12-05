<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Schema;

use Illuminate\Database\Schema\Grammars\Grammar as IlluminateGrammar;
use Illuminate\Support\Fluent;
use LaravelFreelancerNL\FluentAQL\QueryBuilder;

class Grammar extends IlluminateGrammar
{
    /**
     * ArangoDB handles transactions itself. However most schema actions
     * are not done through queries but rather through commands.
     * So we just run schema actions sequentially.
     *
     * @var bool
     */
    protected $transactions = false;

    /**
     * Compile AQL to check if an attribute is in use within a document in the collection.
     * If multiple attributes are set then all must be set in one document.
     *
     * @param string $table
     * @return Fluent
     * @throws BindException
     */
    public function compileColumns($table, Fluent $command)
    {
        $command->bindings = [
            '@collection' => $table,
        ];

        $command->aqb = sprintf(
            'LET rawColumns = MERGE_RECURSIVE(
  (
	FOR doc IN @@collection
		LET fields = ATTRIBUTES(doc, true, true)
		FOR field IN fields
		  RETURN {
				[field]: {
				  [TYPENAME(doc[field])]: true
				}
		  }
  )
)
FOR column IN ATTRIBUTES(rawColumns)
  RETURN {
    name: column,
    types: ATTRIBUTES(rawColumns[column])
  }',
            $table,
        );

        return $command;
    }
    /**
     * Compile AQL to check if an attribute is in use within a document in the collection.
     * If multiple attributes are set then all must be set in one document.
     *
     * @param string $table
     * @return Fluent
     * @throws BindException
     */
    public function compileHasColumn($table, Fluent $command)
    {
        $attributes = $command->getAttributes();

        $aqb = new QueryBuilder();

        $filter = [];
        foreach ($attributes['columns'] as $column) {
            $filter[] = [$aqb->rawExpression('HAS(doc, \'' . $column . '\')')];
        }

        $command->aqb =
            $aqb->let(
                'columnFound',
                $aqb->first(
                    (new QueryBuilder())->for('doc', $table)
                        ->filter($filter)
                        ->limit(1)
                        ->return('true'),
                ),
            )->return($aqb->rawExpression('columnFound == true'))
                ->get();

        return $command;
    }

    /**
     * Compile AQL to rename an attribute, if the new name isn't already in use.
     *
     * @param string $table
     * @param Fluent $command
     * @return Fluent
     */
    public function compileRenameAttribute($table, Fluent $command)
    {
        $attributes = $command->getAttributes();

        $filter = [
            ['doc.' . $attributes['from'], '!=', null],
            ['doc.' . $attributes['to'], '==', null],
        ];

        $aqb = (new QueryBuilder())->for('doc', $table)
            ->filter($filter)
            ->update(
                'doc',
                [
                    $attributes['from'] => null,
                    $attributes['to'] => 'doc.' . $command->from,
                ],
                $table,
            )
            ->options(['keepNull' => false])
            ->get();

        $command->aqb = $aqb;

        return $command;
    }

    /**
     * Compile AQL to drop one or more attributes.
     *
     * @param string $table
     * @param Fluent $command
     * @return Fluent
     */
    public function compileDropColumn($table, Fluent $command)
    {
        $filter = [];
        $attributes = $command->getAttributes();

        $data = [];
        foreach ($attributes['attributes'] as $attribute) {
            $filter[] = ['doc.' . $attribute, '!=', null, 'OR'];
            $data[$attribute] = null;
        }
        $aqb = (new QueryBuilder())->for('doc', $table)
            ->filter($filter)
            ->update('doc', $data, $table)
            ->options(['keepNull' => false])
            ->get();

        $command->aqb = $aqb;

        return $command;
    }

    /**
     * Prepare a bindVar for inclusion in an AQL query.
     */
    public function wrapBindVar(string $attribute): string
    {
        $attribute = trim($attribute);
        if (strpos($attribute, '@') === 0) {
            $attribute = "`$attribute`";
        }

        return $attribute;
    }
}
