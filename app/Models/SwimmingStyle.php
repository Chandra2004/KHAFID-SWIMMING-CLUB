<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwimmingStyle extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'uid',
        'name',
        'code',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uid)) {
                $model->uid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
