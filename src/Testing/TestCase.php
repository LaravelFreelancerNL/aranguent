<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Testing;

use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use LaravelFreelancerNL\Aranguent\Testing\Concerns\PreparesTestingTransactions;

abstract class TestCase extends IlluminateTestCase
{
    use PreparesTestingTransactions;
    use Concerns\InteractsWithDatabase;
}
