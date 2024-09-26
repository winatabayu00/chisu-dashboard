<?php

namespace Winata\Core\Response\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exception extends Model
{
    use HasUuids;
    protected $table = 'exceptions';

    protected static function booted()
    {
        // Set default values
        static::creating(function ($model) {
            $performer = 'system';
            $performer_id = null;
            if (auth()->check()){
                $performer = 'user';
                $performer_id = auth()->user()->id;
            }
            $column = config('winata.response.performer.column');
            $model->performer = $performer;
            $model->$column = $performer_id;
        });
    }

    protected $fillable = [
        'rc',
        'data',
        'source',
        'message',
        'code',
        'file',
        'line',
        'trace',
        'url',
        'ip',
    ];

    protected $hidden = [
        'id', 'performed_by'
    ];

    protected $casts = [
        'data' => 'json'
    ];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(config('winata.response.performer.model'), config('winata.response.performer.column'));
    }
}
