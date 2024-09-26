<?php

namespace Winata\PackageBased\Database\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\Relation;

trait BeforeDeletion
{
    /**
     * @return bool|null
     * @throws \Exception
     */
    public function delete(): ?bool
    {
        $this->beforeDeletion();
        return parent::delete();
    }

    /**
     * @return array
     */
    public function getRelatedModels(): ?array
    {
        return (property_exists($this, 'relationChecking') ? $this->relationChecking : []);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function beforeDeletion(): void
    {
        $relatedModels = $this->getRelatedModels();
        foreach ($relatedModels as $relation){
            if ($this->hasRelation($relation) && $this->hasRelatedData($relation)) {
                throw new \Exception(message: "Cannot delete because related data exists in '{$relation}'.");
            }
        }
    }

    /**
     * @param $relationName
     * @return bool
     */
    private function hasRelation($relationName): bool
    {
        return method_exists($this, $relationName) && $this->{$relationName}() instanceof Relation;
    }

    /**
     * @param $relationName
     * @return mixed
     */
    private function hasRelatedData($relationName): mixed
    {
        return $this->{$relationName}()->exists();
    }
}
