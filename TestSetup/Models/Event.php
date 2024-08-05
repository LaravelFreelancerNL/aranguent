<?php

namespace TestSetup\Models;

use LaravelFreelancerNL\Aranguent\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'id',
        'name',
        'type',
        'age',
        'timeline',
    ];
}
