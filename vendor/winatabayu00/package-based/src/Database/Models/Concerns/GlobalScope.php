<?php

namespace Winata\PackageBased\Database\Models\Concerns;

use Winata\PackageBased\Enums\Table\ScopeTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Winata\Core\Response\Exception\BaseException;

trait GlobalScope
{
    /**
     * @return void
     * @throws BaseException
     */
    protected static function booted(): void
    {
        if (Schema::hasColumn((new self())->getTable(), 'is_active')) {
            static::addGlobalScope(ScopeTable::SCOPE_TABLE_CONTAIN_COLUMN_IS_ACTIVE->value, function (Builder $builder) {
                $builder->where('is_active', '=', true);
            });
        }

    }
}
