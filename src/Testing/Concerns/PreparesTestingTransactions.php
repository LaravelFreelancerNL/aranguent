<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing\Concerns;

trait PreparesTestingTransactions
{
    /**
     * @var array<string, array<string>>
     */
    protected $transactionCollections = [];

    /**
     * @param  array<string, array<string>>  $transactionCollections
     */
    public function setTransactionCollections($transactionCollections): void
    {
        $this->transactionCollections = $transactionCollections;
    }
}
