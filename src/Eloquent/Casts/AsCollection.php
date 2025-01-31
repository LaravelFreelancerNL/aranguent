<?php

declare(strict_types=1);

namespace LaravelFreelancerNL\Aranguent\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\AsCollection as IlluminateAsCollection;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LaravelFreelancerNL\Aranguent\Eloquent\Model;

/**
 * @SuppressWarnings(PHPMD.UndefinedVariable)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AsCollection extends IlluminateAsCollection
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array<array-key, mixed>  $arguments
     * @return \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection<array-key, mixed>, iterable<mixed>>
     */
    public static function castUsing(array $arguments)
    {
        return new class ($arguments) implements CastsAttributes {
            /**
             * @param array<array-key, mixed> $arguments
             */
            public function __construct(protected array $arguments) {}

            /**
             * @param $model
             * @param $key
             * @param $value
             * @param $attributes
             * @return Collection|mixed|void|null
             *
             * @SuppressWarnings(PHPMD.UndefinedVariable)
             */
            public function get($model, $key, $value, $attributes)
            {
                if (! isset($attributes[$key])) {
                    return;
                }

                $data = $attributes[$key];

                if (is_object($data)) {
                    $data = (array) $data;
                }


                $collectionClass = $this->arguments[0] ?? Collection::class;

                if (! is_a($collectionClass, Collection::class, true)) {
                    throw new InvalidArgumentException('The provided class must extend [' . Collection::class . '].');
                }

                return is_array($data) ? new $collectionClass($data) : null;
            }

            /**
             * @param Model $model
             * @param string $key
             * @param mixed $value
             * @param mixed[] $attributes
             * @return mixed[]
             *
             * @SuppressWarnings(PHPMD.UnusedFormalParameter)
             */
            public function set($model, $key, $value, $attributes)
            {
                return [$key => $value];
            }
        };
    }

    /**
     * Specify the collection for the cast.
     *
     * @param  class-string  $class
     * @return string
     */
    public static function using($class)
    {
        return static::class . ':' . $class;
    }
}
