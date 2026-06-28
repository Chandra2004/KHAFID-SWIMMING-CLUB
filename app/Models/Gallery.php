<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'galleries';
    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'event_uid',
        'title',
        'description',
        'cover_image',
        'images',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_uid', 'uid');
    }
}
