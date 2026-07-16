<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasUuids;

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uid'];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'username',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the personal data associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(DataUser::class, 'user_uid', 'uid');
    }

    /**
     * Get primary role name (for blade compatibility)
     */
    public function getNamaRoleAttribute()
    {
        return $this->roles->first()?->name ?? 'user';
    }

    /**
     * Get list of missing profile fields.
     */
    public function getMissingProfileFields(): array
    {
        $missing = [];
        $profile = $this->profile;

        if (empty($this->username)) $missing[] = 'Username';
        if (empty($this->email)) $missing[] = 'Email';

        if (!$profile) {
            $missing[] = 'Data Profil Dasar';
            return $missing;
        }

        if (empty($profile->full_name)) $missing[] = 'Nama Lengkap';
        if (empty($profile->nickname)) $missing[] = 'Nama Panggilan';
        if (empty($profile->phone_number)) $missing[] = 'No. Telepon';
        if (empty($profile->birth_place)) $missing[] = 'Tempat Lahir';
        if (empty($profile->birth_date)) $missing[] = 'Tanggal Lahir';
        if (empty($profile->gender)) $missing[] = 'Jenis Kelamin';
        if (empty($profile->identity_number)) $missing[] = 'NIK / No. KTP';
        if (empty($profile->address)) $missing[] = 'Alamat Lengkap';
        if (empty($profile->club_uid)) $missing[] = 'Klub / Asal Sekolah';
        if (empty($profile->profile_picture)) $missing[] = 'Foto Profil';
        if (empty($profile->identity_photo)) $missing[] = 'Foto KTP / Identitas';
        if (empty($profile->birth_certificate_photo)) $missing[] = 'Foto Akta Kelahiran';
        if (empty($profile->family_card_photo)) $missing[] = 'Foto Kartu Keluarga (KK)';

        return $missing;
    }

    /**
     * Check if profile is complete.
     */
    public function isProfileComplete(): bool
    {
        return empty($this->getMissingProfileFields());
    }
}
