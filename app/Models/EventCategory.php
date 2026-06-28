<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'event_uid',
        'category_uid',
        'acara_number',
        'acara_name',
        'parameter_uid',
        'operator',
        'parameter_value',
        'main_requirement',
        'type',
        'registration_fee',
        'total_series',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'group_link',
    ];

    protected $casts = [
        'registration_fee' => 'decimal:2',
        'total_series' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uid) {
                $model->uid = (string) Str::uuid();
            }
        });

        static::deleting(function ($eventCategory) {
            $eventCategory->requirements()->delete();
        });

        static::restoring(function ($eventCategory) {
            $eventCategory->requirements()->withTrashed()->restore();
        });
    }

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_uid', 'uid');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_uid', 'uid');
    }

    public function requirements()
    {
        return $this->hasMany(CategoryRequirement::class, 'event_category_uid', 'uid');
    }
}
