<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAttribute extends Model
{
    use HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'user_uid',
        'name',
        'type',
        'value',
        'is_required',
        'sort_order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }
}
