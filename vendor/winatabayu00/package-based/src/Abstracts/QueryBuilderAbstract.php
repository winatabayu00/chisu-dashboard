<?php

namespace Winata\PackageBased\Abstracts;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

abstract class QueryBuilderAbstract
{
    /**
     * Base Query Builder.
     *
     * @var Builder
     */
    protected Builder $builder;

    /**
     * Parameters to excludes.
     *
     * @var array
     */
    protected array $excepts;

    /**
     * Default date column.
     *
     * @var string
     */
    protected string $dateColumn = 'created_at';

    private bool $caseSensitive = false;

    /**
     * Abstract Query Builder constructor.
     *
     * @param array $inputs
     * @param Builder|null $builder
     * @param array $excepts
     */
    public function __construct(
        public readonly array $inputs = [],
        Builder               $builder = null,
        array                 $excepts = []
    )
    {
        $this->builder = $builder ?? $this->getBaseQuery();
        $this->excepts = $excepts;
    }

    /**
     * Get Base Query Builder.
     *
     * @return Builder
     */
    abstract public function getBaseQuery(): Builder;

    /**
     * Apply additional callback if necessary.
     *
     * @param Closure $callback
     *
     * @return QueryBuilderAbstract
     */
    public function callback(Closure $callback): self
    {
        $this->builder = $callback($this->builder);

        return $this;
    }

    /**
     * Get final build for builder.
     *
     * @return Builder
     * @throws ValidationException
     */
    public function build(): Builder
    {
        $this->common()
            ->applyOrderable()
            ->applyParameters();

        return $this->builder;
    }

    /**
     * Apply common parameters.
     *
     * @return QueryBuilderAbstract
     *
     * @throws ValidationException
     */
    public function common(): self
    {
        $operator = 'ilike';
        if ($this->caseSensitive){
            $operator = 'like';
        }
        $this->builder->when(false === empty(request()?->input('q')), function ($query) use ($operator) {
            foreach ($query->getModel()->searchable ?? [] as $key => $column) {
                if ($key === 0) {
                    $query->where($column, $operator, '%' . request()->input('q') . '%');
                } else {
                    $query->orWhere($column, $operator, '%' . request()->input('q') . '%');
                }
            }
        });

        $this->builder->when(request()->input('search.value') !== null, function (Builder $query) use ($operator) {
            foreach ($query->getModel()->searchable ?? [] as $key => $column) {

                if ($key === 0) {
                    $query->where($column, $operator, '%' . request()->input('search.value') . '%');
                } else {
                    $query->orWhere($column, $operator, '%' . request()->input('search.value') . '%');
                }
            }
        });

        $this->builder->when(false === empty(request()?->input('date_from')),
            function ($q) {
                try {
                    $date = Carbon::createFromFormat('Y-m-d', request()?->input('date_from'));
                } catch (Exception $e) {
                    throw ValidationException::withMessages(['date_from' => 'Date format should like yyyy-mm-dd']);
                }

                $q->where($this->dateColumn, '>=', $date->startOfDay());
            }
        //            fn ($q) => $q->whereDate($this->dateColumn, '>=', Carbon::createFromTimeString(request()?->input('date_from')))
        );
        $this->builder->when(false === empty(request()?->input('date_to')),
            function ($q) {
                try {
                    $date = Carbon::createFromFormat('Y-m-d', request()?->input('date_to'));
                } catch (Exception $e) {
                    throw ValidationException::withMessages(['date_to' => 'Date format should like yyyy-mm-dd']);
                }

                $q->where($this->dateColumn, '<=', $date->endOfDay());
            }
        //            fn ($q) => $q->whereDate($this->dateColumn, '<=', Carbon::createFromTimeString(request()?->input('date_to')))
        );

        $this->builder->when(false === empty(request()?->input('order_by')), fn(Builder $b) => $b->orderBy(request('order_by'), request('order', 'DESC')));

        return $this;
    }

    /**
     * @return $this
     */
    public function applyOrderable(): static
    {
        $this->builder->when(isset($this->builder->getModel()->orderable), function (Builder $query) {
            foreach ($query->getModel()->orderable as $key => $sort) {
                $query->orderBy($key, $sort);
            }
        }, function (Builder $query) {
            $query->orderBy($this->dateColumn, 'desc');
        });
        return $this;
    }

    /**
     * Apply Filtered parameters for builder.
     *
     * @return void
     */
    abstract protected function applyParameters(): void;
}
