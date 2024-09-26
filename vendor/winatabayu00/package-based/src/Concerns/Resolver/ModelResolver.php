<?php

namespace Winata\PackageBased\Concerns\Resolver;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ReflectionClass;
use ReflectionException;
use Veelasky\LaravelHashId\Eloquent\HashableId;

trait ModelResolver
{
    /**
     * Model resolver.
     *
     * @param class-string $model
     * @param string|int|Model $key
     * @param bool $strict
     *
     * @return Model|null
     *
     * @throws ReflectionException
     * @throws ModelNotFoundException
     */
    public function resolveModel(string $model, mixed $key, bool $strict = true): ?Model
    {
        if (is_object($key) && is_a($key, $model)) {
            return $key;
        }

        $reflection = new ReflectionClass($model);

        $found = null;

        if (in_array(HashableId::class, $reflection->getTraitNames()) && !is_null($key)) {
            $found = $model::byHash($key);
        }

        if (is_null($found)) {
            $found = $model::find($key);
        }

        if ($strict && false === $found instanceof Model) {
            throw (new ModelNotFoundException())->setModel($model);
        }

        // as is.
        return $found;
    }

    /**
     * @param string $model
     * @param string|int $key
     *
     * @return int|string|null
     *
     * @throws ReflectionException
     */
    public function resolveModelKey(string $model, string|int $key): int|string|null
    {
        $reflection = new ReflectionClass($model);

        $found = null;

        if (in_array(HashableId::class, $reflection->getTraitNames()) && is_string($key) && !is_null($key)) {
            $found = $model::hashToId($key);
        }

        if (is_null($found)) {
            $found = $key;
        }

        // as is.
        return $found;
    }
}
