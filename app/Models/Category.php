<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'slug',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_categories', 'category_uid', 'event_uid')
                    ->withPivot('uid', 'event_number', 'event_name', 'registration_fee');
    }
}
