<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Schema\Concerns;

use Illuminate\Support\Fluent;

trait TableCommands
{
    /**
     * Indicate that the table needs to be created.
     *
     * @param  array  $options
     * @return Fluent
     */
    public function create($options = [])
    {
        $parameters = [];
        $parameters['options'] = $options;
        $parameters['explanation'] = "Create '{$this->table}' table.";
        $parameters['handler'] = 'table';

        return $this->addCommand('create', $parameters);
    }

    /**
     * @param string $command
     * @param mixed[] $args
     * @return void
     */
    public function handleKeyCommands($command, $args)
    {
        $acceptedKeyFields = ['id', '_id', '_key'];

        $columns = ($command === 'autoIncrement') ? end($this->columns) : $args;
        $columns = (is_array($columns)) ? $columns : [$columns];

        if (count($columns) !== 1 || ! in_array($columns[0], $acceptedKeyFields)) {
            return;
        }

        if ($command === 'uuid') {
            $this->keyGenerator = 'uuid';

            return;
        }

        if (config('arangodb.schema.key_handling.use_traditional_over_autoincrement') === false) {
            $this->keyGenerator = 'autoincrement';

            return;
        }

        $this->keyGenerator = 'traditional';
    }

    public function executeCreateCommand($command)
    {
        if ($this->connection->pretending()) {
            $this->connection->logQuery('/* ' . $command->explanation . " */\n", []);

            return;
        }

        $options = $command->options;
        $options['keyOptions'] = $this->setKeyOptions($options['keyOptions'] ?? []);

        if (!$this->schemaManager->hasCollection($this->table)) {
            $this->schemaManager->createCollection($this->table, $options);
        }
    }
}
