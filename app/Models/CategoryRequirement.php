<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CategoryRequirement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'event_category_uid',
        'parameter_uid',
        'parameter_name',
        'parameter_type',
        'parameter_value',
        'operator',
        'is_main',
        'is_required',
        'priority',
        'error_message',
        'notes',
    ];

    protected $casts = [
        'parameter_value' => 'array',
        'is_main' => 'boolean',
        'is_required' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uid) {
                $model->uid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function eventCategory()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_uid', 'uid');
    }

    public function parameter()
    {
        return $this->belongsTo(RequirementParameter::class, 'parameter_uid', 'uid');
    }
}
