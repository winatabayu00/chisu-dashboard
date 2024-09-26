<?php

namespace Winata\PackageBased\Database\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;

trait SoftDeletes
{
    use BaseSoftDeletes;

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete(): void
    {

        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if (config('winata.core.performable')) {
            $this->setPerformedBy('performer_on_delete');
            $columns[$this->getDeletedByColumn()] = $this->performBy;
            $this->{$this->getDeletedByColumn()} = $this->performBy;
        }

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);

            if (config('winata.core.performable')) {
                $this->{$this->getUpdatedByColumn()} = $this->performBy;
                $columns[$this->getUpdatedByColumn()] = $this->performBy;
            }
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('trashed', false);
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    public function restore(): ?bool
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->{$this->getDeletedAtColumn()} = null;
        $this->{$this->getRestoreAtColumn()} = $this->freshTimestamp();

        if (config('winata.core.performable')) {
            $this->setPerformedBy('performer_on_restore');
            $this->{$this->getDeletedByColumn()} = null;
            $this->{$this->getRestoreByColumn()} = $this->performBy;
        }

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getDeletedByColumn(): string
    {
        return defined('static::DELETED_BY') ? static::DELETED_BY : config('winata.package-based.performable_columns.on_delete');
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getRestoreAtColumn(): string
    {
        return defined('static::RESTORE_AT') ? static::RESTORE_AT : 'restore_at';
    }

    /**
     * Get the name of the "deleted by" column.
     *
     * @return string
     */
    public function getRestoreByColumn(): string
    {
        return defined('static::RESTORE_BY') ? static::RESTORE_BY : config('winata.package-based.performable_columns.on_restore');
    }

    /**
     * Get the fully qualified "deleted by" column.
     *
     * @return string
     */
    public function getQualifiedDeletedByColumn(): string
    {
        return $this->qualifyColumn($this->getDeletedByColumn());
    }

    /**
     * Get the fully qualified "restore at" column.
     *
     * @return string
     */
    public function getQualifiedRestoreAtColumn(): string
    {
        return $this->qualifyColumn($this->getRestoreAtColumn());
    }

    /**
     * Get the fully qualified "restore by" column.
     *
     * @return string
     */
    public function getQualifiedRestoreByColumn(): string
    {
        return $this->qualifyColumn($this->getRestoreByColumn());
    }
}
