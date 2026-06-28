<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialMedia extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'social_medias';
    protected $primaryKey = 'uid';
    public $incrementing = false;

    protected $fillable = [
        'user_uid',
        'platform',
        'link',
        'icon',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }
}
