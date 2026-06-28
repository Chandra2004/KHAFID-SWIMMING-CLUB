<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccessManagementController extends Controller
{
    public function user()
    {
        $users = User::with(['profile', 'roles', 'profile.club'])->get()->map(function (User $user) {
            return [
                'uid' => $user->uid,
                'nama_lengkap' => $user->profile->full_name ?? $user->username,
                'email' => $user->email,
                'no_telepon' => $user->profile->phone_number ?? '-',
                'nama_role' => $user->getRoleNames()->first() ?? '-',
                'uid_role' => $user->roles->first()->uid ?? null,
                'nama_klub' => $user->profile->club->name ?? null,
                'tanggal_lahir' => $user->profile->birth_date ? $user->profile->birth_date->format('d M Y') : '-',
                'foto_profil' => $user->profile->profile_picture ?? null,
                'foto_ktp' => $user->profile->identity_photo ?? null,
                'alamat' => $user->profile->address ?? '-',
                'jenis_kelamin' => $user->profile->gender ?? '-',
                'is_active' => $user->is_active,
            ];
        });

        $roles = Role::all()->map(function ($role) {
            return [
                'uid' => $role->uid,
                'nama_role' => $role->name,
            ];
        });

        $data = [
            'title' => 'Manajemen Pengguna | Khafid Swimming Club',
            'users' => $users,
            'roles' => $roles,
        ];

        return view('dashboard.access.user', $data);

    }

    public function role()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        $data = [
            'title' => 'Manajemen Hak Akses | Khafid Swimming Club',
            'roles' => $roles,
            'permissions' => $permissions,
        ];

        return view('dashboard.access.role', $data);

    }

    public function permission()
    {
        $permissions = Permission::all();

        $data = [
            'title' => 'Manajemen Izin Akses | Khafid Swimming Club',
            'permissions' => $permissions,
        ];

        return view('dashboard.access.permission', $data);

    }
}
