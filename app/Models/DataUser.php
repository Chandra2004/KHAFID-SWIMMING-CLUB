<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataUser extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uid'];
    }

    protected $table = 'data_users';

    protected $fillable = [
        'uid',
        'user_uid',
        'full_name',
        'nickname',
        'gender',
        'birth_date',
        'birth_place',
        'phone_number',
        'backup_phone_number',
        'address',
        'height',
        'weight',
        'identity_number',
        'profile_picture',
        'identity_photo',
        'birth_certificate_photo',
        'family_card_photo',
        'medical_history',
        'club_uid',
        'is_active',
        'joined_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'birth_date' => 'date',
        'joined_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_uid', 'uid');
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_uid', 'uid');
    }
}
