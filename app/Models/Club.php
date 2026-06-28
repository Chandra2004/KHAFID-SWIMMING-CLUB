<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'short_name',
        'logo',
        'coach_name',
        'contact',
        'address',
        'website',
    ];

    public function members()
    {
        return $this->hasMany(DataUser::class, 'club_uid', 'uid');
    }
}
