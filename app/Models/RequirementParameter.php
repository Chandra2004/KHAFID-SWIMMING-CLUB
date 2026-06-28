<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RequirementParameter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'parameter_key',
        'source',
        'display_name',
        'input_type',
        'input_options',
        'validation_rules',
        'error_message',
        'allowed_operators',
        'description',
        'is_active',
    ];

    protected $casts = [
        'input_options' => 'array',
        'allowed_operators' => 'array',
        'is_active' => 'boolean',
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
}
