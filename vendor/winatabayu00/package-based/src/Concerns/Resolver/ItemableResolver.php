<?php

namespace Winata\PackageBased\Concerns\Resolver;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use ReflectionException;
use Winata\PackageBased\Contracts\ItemableInterface;

trait ItemableResolver
{
    use ModelResolver;

    /**
     * @param string $type
     * @param int|string $identifier
     *
     * @return ItemableInterface|Model
     *
     * @throws ReflectionException
     */
    protected function getItemable(string $type, int|string $identifier): ItemableInterface|Model
    {
        $modelString = config('resolver.bill-item.' . $type . '.model');

        abort_unless($modelString, 404, "itemable for {$type} not found, did you forget to register it in config/bill-item.php");

        /** @var ItemableInterface $model */
        $model = $this->resolveModel($modelString, $identifier);

        if (!$model instanceof Model) {
            throw new InvalidArgumentException('Class ' . $modelString . 'must be instance of ' . Model::class);
        }


        return $model;
    }
}
