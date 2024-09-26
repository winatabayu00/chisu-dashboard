<?php

namespace Winata\PackageBased\Database\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps as BaseHasTimestamps;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Fluent;

trait WorkingWithPerformable
{
    use BaseHasTimestamps;

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setCreatedAt($value): static
    {
        $this->{$this->getCreatedAtColumn()} = $value;
        if (config('winata.package-based.performable')) {
            $this->setPerformedBy('performer_on_create');
            $this->{$this->getCreatedByColumn()} = $this->performBy;
        }

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setUpdatedAt($value): static
    {
        $this->{$this->getUpdatedAtColumn()} = $value;
        if (config('winata.package-based.performable')) {
            $this->setPerformedBy('performer_on_update');
            $this->{$this->getUpdatedByColumn()} = $this->performBy;
        }

        return $this;
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getCreatedByColumn(): string
    {
        return defined(constant_name: 'static::CREATED_BY') ? static::CREATED_BY : config('winata.package-based.performable_columns.on_create');
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function getUpdatedByColumn(): string
    {
        return defined(constant_name: 'static::UPDATED_BY') ? static::UPDATED_BY : config('winata.package-based.performable_columns.on_update');
    }

    /**
     * Creator of the relationship.
     *
     * @return BelongsTo|Fluent
     */
    public function creator(): BelongsTo|Fluent
    {
        return $this->belongsTo(config('winata.package-based.model.users'), $this->getCreatedByColumn());
    }

    /**
     * Updater of the relationship.
     *
     * @return BelongsTo|Fluent
     */
    public function updater(): BelongsTo|Fluent
    {
        return $this->belongsTo(config('winata.package-based.model.users'), $this->getUpdatedByColumn());
    }
}
