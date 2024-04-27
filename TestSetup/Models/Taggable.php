<?php

namespace TestSetup\Models;

use LaravelFreelancerNL\Aranguent\Eloquent\Relations\MorphPivot;

class Taggable extends MorphPivot
{
    protected $table = 'taggables';
}
