<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Facades;

use Closure;
use Illuminate\Support\Facades\Schema as IlluminateSchema;
use LaravelFreelancerNL\Aranguent\Schema\Builder;

/**
 * Table handling:
 *
 * @method static Builder create($collection, Closure $callback, $options = [])
 * @method static Builder getTables()
 * @method static Builder drop(string $collection)
 * @method static Builder dropIfExists(string $collection)
 * @method static Builder dropAllTables()
 * @method static Builder table(string $table, \Closure $callback)
 * @method static Builder hasColumn(string $table, $column)
 *
 * View handling:
 * @method static Builder createView($name, array $properties, $type = 'arangosearch')
 * @method static Builder hasView(string $name)
 * @method static Builder getView(string $name)
 * @method static Builder getViews()
 * @method static Builder editView($name, array $properties)
 * @method static Builder renameView(string $from, string $to)
 * @method static Builder dropView(string $name)
 * @method static Builder dropAllViews()
 *
 * Analyzer handling:
 * @method static Builder createAnalyzer($name, array $properties)
 * @method static Builder hasAnalyzer()
 * @method static Builder getAnalyzer(string $name)
 * @method static Builder getAnalyzers()
 * @method static Builder replaceAnalyzer($name, array $properties)
 * @method static Builder dropAnalyzer(string $name)
 * @method static Builder dropAnalyzerIfExists(string $name)
 * @method static Builder dropAllAnalyzers()
 *
 * Named Graph handling:
 * @method static Builder creategraph(string $name, array $properties = [], bool $waitForSync = false)
 * @method static Builder hasGraph(string $name)
 * @method static Builder getGraph(string $name)
 * @method static Builder getGraphs()
 * @method static Builder dropGraph(string $name)
 * @method static Builder dropGraphIfExists(string $name)
 * @method static Builder dropAllGraphs()
 *
 * @see \LaravelFreelancerNL\Aranguent\Schema\Builder
 */
class Schema extends IlluminateSchema {}
