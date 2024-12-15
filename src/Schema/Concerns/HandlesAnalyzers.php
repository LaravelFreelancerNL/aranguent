<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Schema\Concerns;

use ArangoClient\Exceptions\ArangoException;

trait HandlesAnalyzers
{
    /**
     * @param  array<mixed>  $properties
     * @param  array<string>  $features
     *
     * @throws ArangoException
     */
    public function createAnalyzer(string $name, string $type, array $properties = null, array $features = null)
    {
        $analyzer = array_filter([
            'name' => $name,
            'type' => $type,
            'properties' => $properties,
            'features' => $features,
        ]);

        $this->schemaManager->createAnalyzer($analyzer);
    }

    /**
     * @param  array<mixed>  $properties
     * @param  array<string>  $features
     *
     * @throws ArangoException
     */
    public function replaceAnalyzer(string $name, string $type, array $properties = null, array $features = null)
    {
        $analyzer = array_filter([
            'name' => $name,
            'type' => $type,
            'properties' => $properties,
            'features' => $features,
        ]);

        $this->schemaManager->replaceAnalyzer($name, $analyzer);
    }

    /**
     * @param string $name
     * @return mixed[]
     * @throws ArangoException
     */
    public function getAnalyzer(string $name): array
    {
        return (array) $this->schemaManager->getAnalyzer($name);
    }

    public function hasAnalyzer(string $analyzer): bool
    {
        return $this->handleExceptionsAsQueryExceptions(function () use ($analyzer) {
            return $this->schemaManager->hasAnalyzer($analyzer);
        });
    }

    /**
     * @throws ArangoException
     */
    public function getAnalyzers(): array
    {
        return $this->mapResultsToArray(
            $this->schemaManager->getAnalyzers(),
        );
    }

    /**
     * @throws ArangoException
     */
    public function dropAnalyzer(string $name)
    {
        $this->schemaManager->deleteAnalyzer($name);
    }

    /**
     * @throws ArangoException
     */
    public function dropAnalyzerIfExists(string $name): bool
    {
        if ($this->hasAnalyzer($name)) {
            $this->schemaManager->deleteAnalyzer($name);
        }

        return true;
    }

    /**
     * Drop all custom analyzers from the schema.
     *
     * @throws ArangoException
     */
    public function dropAllAnalyzers(): void
    {
        $this->schemaManager->deleteAllAnalyzers();
    }
}
