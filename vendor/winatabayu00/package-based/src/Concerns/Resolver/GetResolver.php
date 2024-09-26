<?php

namespace Winata\PackageBased\Concerns\Resolver;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use ReflectionException;

trait GetResolver
{
    use ModelResolver;

    /**
     * @param string $configName
     * @param string $type
     * @param int|string $identifier
     *
     * @return Model
     *
     * @throws ReflectionException
     */
    protected function resolve(string $configName, string $type, int|string $identifier): Model
    {
        $modelString = config('resolver.' . $configName . '.' . $type . '.model');

        abort_unless($modelString, 404, "itemable for {$type} not found, did you forget to register it in config/resolver/{$configName}.php");

        $model = $this->resolveModel($modelString, $identifier);

        if (!$model instanceof Model) {
            throw new InvalidArgumentException('Class ' . $modelString . 'must be instance of ' . Model::class);
        }

        return $model;
    }
}

