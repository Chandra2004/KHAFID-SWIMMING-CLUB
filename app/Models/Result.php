<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'registration_uid',
        'final_time',
        'total_milliseconds',
        'status',
        'rank',
        'official_name',
        'score',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'registration_uid', 'uid');
    }
}
