<?php

namespace TestSetup\Models;

use LaravelFreelancerNL\Aranguent\Eloquent\Relations\Pivot;

class Child extends Pivot
{
    protected $table = 'children';
}
