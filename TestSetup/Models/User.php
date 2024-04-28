<?php

namespace TestSetup\Models;

use LaravelFreelancerNL\Aranguent\Eloquent\Casts\AsArrayObject;
use LaravelFreelancerNL\Aranguent\Eloquent\Casts\AsCollection;

class User extends \LaravelFreelancerNL\Aranguent\Auth\User
{
    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'uuid',
        'is_admin',
        'profileAsArray',
        'profileAsArrayObjectCast',
        'profileAsObject',
        'favoritesCollection',
        'favoritesAsCollectionCast',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'profileAsArray' => 'array',
            'profileAsArrayObjectCast' => AsArrayObject::class,
            'profileAsObject' => 'object',
            'profileAsJson' => 'json',
            'favoritesCollection' => 'collection',
            'favoritesAsCollectionCast' => AsCollection::class,
        ];
    }
}
