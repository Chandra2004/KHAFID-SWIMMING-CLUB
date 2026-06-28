<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasUuids;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'event_uid',
        'key',
        'type',
        'value',
        'description',
        'is_editable',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_uid', 'uid');
    }
}
