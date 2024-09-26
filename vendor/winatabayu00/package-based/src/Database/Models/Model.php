<?php

namespace Winata\PackageBased\Database\Models;

use Winata\PackageBased\Database\Models\Concerns\BeforeDeletion;
use Winata\PackageBased\Database\Models\Concerns\GlobalScope;
use Winata\PackageBased\Database\Models\Concerns\SoftDeletes;
use Winata\PackageBased\Database\Models\Concerns\WorkingWithPerformable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Support\Facades\DB;

class Model extends LaravelModel
{
    use GlobalScope, WorkingWithPerformable, BeforeDeletion;

    /**
     * The list of table which include with schema.
     */
    protected string|array $fullTableName = [];

    /**
     * Who is (user) as executor.
     */
    protected ?string $performBy = null;

    /** @inheritdoc */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $schema = DB::getDatabaseName();

        $this->fullTableName['self'] = "{$schema}.{$this->table}";
    }


    /**
     * @param array $data
     *
     * @return array
     */
    public static function getFillableAttribute(array $data): array
    {
        $fillable = (new static)->getFillable();

        return Arr::only($data, Arr::flatten($fillable));
    }

    /**
     * set performer from performer.
     *
     * @param string $event
     * @return void
     */
    protected function setPerformedBy(string $event): void
    {
        if ((config("winata.core.using_performable.{$event}") ?? false) === false){
            return;
        }
        // reset performer
        $this->performBy = null;

        if (auth()->check() && config('winata.core.performable') && empty($this->performBy)) {
            /** @var Authenticatable $user */
            $user = auth()->user();
            if (!empty($user)) {
                $this->performBy = $user->id;
            }
        }
    }
}
