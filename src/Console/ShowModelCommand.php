<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Console;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Console\ShowModelCommand as IlluminateShowModelCommand;
use Illuminate\Database\Eloquent\ModelInspector;
use Illuminate\Support\Collection;

class ShowModelCommand extends IlluminateShowModelCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'model:show {model : The model to show}
                {--database= : The database connection to use}
                {--json : Output the model as JSON}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ModelInspector $modelInspector)
    {
        try {
            $info = $modelInspector->inspect(
                $this->argument('model'),
                $this->option('database'),
            );
        } catch (BindingResolutionException $e) {
            $this->components->error($e->getMessage());

            return 1;
        }

        $this->display(
            $info['class'],
            $info['database'],
            $info['table'],
            $info['policy'],
            $info['attributes'],
            $info['relations'],
            $info['events'],
            $info['observers'],
        );

        return 0;
    }

    /**
     * Render the model information.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $class
     * @param  string  $database
     * @param  string  $table
     * @param  class-string|null  $policy
     * @param  Collection  $attributes
     * @param  Collection  $relations
     * @param  Collection  $events
     * @param  Collection  $observers
     * @return void
     */
    protected function display($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
    {
        $this->option('json')
            ? $this->displayJson($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
            : $this->displayCli($class, $database, $table, $policy, $attributes, $relations, $events, $observers);
    }

    /**
     * Render the model information for the CLI.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $class
     * @param  string  $database
     * @param  string  $table
     * @param  class-string|null  $policy
     * @param  Collection  $attributes
     * @param  Collection  $relations
     * @param  Collection  $events
     * @param  Collection  $observers
     * @return void
     */
    protected function displayCli($class, $database, $table, $policy, $attributes, $relations, $events, $observers)
    {
        $this->newLine();

        $this->components->twoColumnDetail('<fg=green;options=bold>' . $class . '</>');
        $this->components->twoColumnDetail('Database', $database);
        $this->components->twoColumnDetail('Table', $table);

        if ($policy) {
            $this->components->twoColumnDetail('Policy', $policy);
        }

        $this->newLine();

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>Attributes</>',
            'type <fg=gray>/</> <fg=yellow;options=bold>cast</>',
        );

        foreach ($attributes as $attribute) {
            $first = trim(sprintf(
                '%s %s',
                $attribute['name'],
                collect(['computed', 'increments', 'unique', 'nullable', 'fillable', 'hidden', 'appended'])
                    ->filter(fn($property) => $attribute[$property])
                    ->map(fn($property) => sprintf('<fg=gray>%s</>', $property))
                    ->implode('<fg=gray>,</> '),
            ));

            $second = collect([
                (is_array($attribute['type'])) ? implode(', ', $attribute['type']) : $attribute['type'],
                $attribute['cast'] ? '<fg=yellow;options=bold>' . $attribute['cast'] . '</>' : null,
            ])->filter()->implode(' <fg=gray>/</> ');

            $this->components->twoColumnDetail($first, $second);
        }

        $this->newLine();

        $this->components->twoColumnDetail('<fg=green;options=bold>Relations</>');

        foreach ($relations as $relation) {
            $this->components->twoColumnDetail(
                sprintf('%s <fg=gray>%s</>', $relation['name'], $relation['type']),
                $relation['related'],
            );
        }

        $this->newLine();

        $this->displayCliEvents($events, $observers);

        $this->newLine();
    }

    /**
     * @param Collection $events
     * @return void
     */
    public function displayCliEvents(Collection $events, Collection $observers): void
    {
        $this->components->twoColumnDetail('<fg=green;options=bold>Events</>');

        if ($events->count()) {
            foreach ($events as $event) {
                $this->components->twoColumnDetail(
                    sprintf('%s', $event['event']),
                    sprintf('%s', $event['class']),
                );
            }
        }

        $this->newLine();

        $this->components->twoColumnDetail('<fg=green;options=bold>Observers</>');

        if ($observers->count()) {
            foreach ($observers as $observer) {
                $this->components->twoColumnDetail(
                    sprintf('%s', $observer['event']),
                    implode(', ', $observer['observer']),
                );
            }
        }

    }

}
