<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'slug',
        'name',
        'banner',
        'logo_left',
        'logo_right',
        'description',
        'location',
        'start_date',
        'end_date',
        'start_time',
        'lane_count',
        'status',
        'author_uid',
        'payment_method_uid',
        'group_link',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'lane_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->uid) {
                $model->uid = (string) Str::uuid();
            }
            if (!$model->slug) {
                $model->slug = Str::slug($model->name) . '-' . Str::random(5);
            }
        });

        static::deleting(function ($event) {
            $event->categories()->delete();
        });

        static::restoring(function ($event) {
            $event->categories()->withTrashed()->restore();
        });
    }

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_uid', 'uid');
    }

    public function categories()
    {
        return $this->hasMany(EventCategory::class, 'event_uid', 'uid');
    }

    public function financeAccount()
    {
        return $this->belongsTo(FinanceAccount::class, 'payment_method_uid', 'uid');
    }
}
