<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. DEFINISI PERMISSIONS
        $permissions = [
            // PERSONAL DATA
            // My Profile
            'my-profile.view',
            'my-profile.edit',
            'my-profile.delete',
            'my-profile.change-password',

            // Notification Management
            'notifications.view',
            'notifications.create',
            'notifications.edit',
            'notifications.delete',

            // DOCUMENT
            'setting-document.view',

            'report-data.view',
            'report-data.export',
            'documents.view-sensitive',

            // MASTER DATA & EVENT
            // Master Management Galeri
            'master-galeri.view',
            'master-galeri.create',
            'master-galeri.edit',
            'master-galeri.delete',

            // Master Management Keuangan
            'master-keuangan.view',
            'master-keuangan.create',
            'master-keuangan.edit',
            'master-keuangan.delete',

            // Master Management Gaya
            'master-gaya.view',
            'master-gaya.create',
            'master-gaya.edit',
            'master-gaya.delete',

            // Master Management Parameter
            'master-parameter.view',
            'master-parameter.create',
            'master-parameter.edit',
            'master-parameter.delete',

            // Master Management Event
            'master-event.view',
            'master-event.create',
            'master-event.edit',
            'master-event.delete',

            // Master management Lomba
            'master-lomba.view',
            'master-lomba.create',
            'master-lomba.edit',
            'master-lomba.delete',

            // Master Management Pendaftaran
            'master-pendaftaran.view',
            'master-pendaftaran.create',
            'master-pendaftaran.edit',
            'master-pendaftaran.delete',
            'master-pendaftaran.view.self',
            'master-pendaftaran.create.self',
            'master-pendaftaran.edit.self',
            'master-pendaftaran.delete.self',
            'master-pendaftaran.pay.cash',
            'master-pendaftaran.filter',

            // Master Management Result
            'master-result.view',
            'master-result.detail',
            'master-result.detail.self',
            'master-result.detail.team',
            'master-result.detail.edit',
            'master-result.detail.delete',

            // Master History Pendaftaran
            'master-history-pendaftaran.view',
            'master-history-pendaftaran.view.self',
            'master-history-pendaftaran.view.all',
            'master-history-pendaftaran.detail',
            'master-history-pendaftaran.export',
            'master-history-pendaftaran.edit',
            'master-history-pendaftaran.delete',

            // Club Management
            'clubs.view',
            'clubs.create',
            'clubs.edit',
            'clubs.delete',

            // Permission Management
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',

            // Role Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Dashboard Card Permissions
            'dashboard.view',

            'dashboard.view-card.finance.self',
            'dashboard.view-card.finance.lomba',
            'dashboard.view-card.finance.event',
            'dashboard.view-card.finance.all',

            'dashboard.view-card.history.self',
            'dashboard.view-card.history.all',

            'dashboard.view-card.message.self',
            'dashboard.view-card.message.all',

            'dashboard.view-card.users',
            'dashboard.view-card.roles',
            'dashboard.view-card.permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ], [
                'uid' => (string) \Illuminate\Support\Str::uuid()
            ]);
        }

        // 2. DEFINISI ROLES & ASSIGN PERMISSIONS

        // SUPERADMIN: Semua hak akses
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web'], ['uid' => (string) \Illuminate\Support\Str::uuid()]);
        $superadmin->givePermissionTo(Permission::all());

        // ADMIN: Mengelola operasional harian
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'], ['uid' => (string) \Illuminate\Support\Str::uuid()]);
        $admin->givePermissionTo([
            // PERSONAL DATA
            // My Profile
            'my-profile.view',
            'my-profile.edit',
            'my-profile.delete',
            'my-profile.change-password',

            // Notification Management
            'notifications.view',
            'notifications.create',
            'notifications.edit',
            'notifications.delete',

            // DOCUMENT
            'report-data.view',
            'report-data.export',
            'documents.view-sensitive',

            // MASTER DATA & EVENT
            // Master Management Galeri
            'master-galeri.view',
            'master-galeri.create',
            'master-galeri.edit',
            'master-galeri.delete',

            // Master Management Keuangan
            'master-keuangan.view',
            'master-keuangan.create',
            'master-keuangan.edit',
            'master-keuangan.delete',

            // Master Management Gaya
            'master-gaya.view',
            'master-gaya.create',
            'master-gaya.edit',
            'master-gaya.delete',

            // Master Management Event
            'master-event.view',
            'master-event.create',
            'master-event.edit',
            'master-event.delete',

            // Master management Lomba
            'master-lomba.view',
            'master-lomba.create',
            'master-lomba.edit',
            'master-lomba.delete',

            // Master Management Pendaftaran
            'master-pendaftaran.view',
            'master-pendaftaran.create',
            'master-pendaftaran.edit',
            'master-pendaftaran.delete',
            'master-pendaftaran.view.self',
            'master-pendaftaran.create.self',
            'master-pendaftaran.edit.self',
            'master-pendaftaran.delete.self',
            'master-pendaftaran.pay.cash',
            'master-pendaftaran.filter',

            // Master Management Result
            'master-result.view',
            'master-result.detail',
            'master-result.detail.self',
            'master-result.detail.team',
            'master-result.detail.edit',
            'master-result.detail.delete',

            // Master History Pendaftaran
            'master-history-pendaftaran.view',
            'master-history-pendaftaran.view.self',
            'master-history-pendaftaran.view.all',
            'master-history-pendaftaran.detail',
            'master-history-pendaftaran.export',
            'master-history-pendaftaran.edit',
            'master-history-pendaftaran.delete',

            // Club Management
            'clubs.view',
            'clubs.create',
            'clubs.edit',
            'clubs.delete',

            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Dashboard Card Permissions
            'dashboard.view',

            'dashboard.view-card.finance.self',
            'dashboard.view-card.finance.lomba',
            'dashboard.view-card.finance.event',
            'dashboard.view-card.finance.all',

            'dashboard.view-card.history.self',
            'dashboard.view-card.history.all',

            'dashboard.view-card.message.self',
            'dashboard.view-card.message.all',

            'dashboard.view-card.users',
            'dashboard.view-card.roles',
            'dashboard.view-card.permissions',
        ]);

        // PELATIH: Mengelola klub dan melihat event
        $coach = Role::firstOrCreate(['name' => 'pelatih', 'guard_name' => 'web'], ['uid' => (string) \Illuminate\Support\Str::uuid()]);
        $coach->givePermissionTo([
            // PERSONAL DATA
            // My Profile
            'my-profile.view',
            'my-profile.edit',
            'my-profile.delete',
            'my-profile.change-password',

            // Notification Management
            'notifications.view',
            'notifications.delete',

            // DOCUMENT
            'documents.view-sensitive',

            // Master Management Pendaftaran
            'master-pendaftaran.view',
            'master-pendaftaran.filter',

            // Master Management Result
            'master-result.view',
            'master-result.detail',

            // Master History Pendaftaran
            'master-history-pendaftaran.view',
            'master-history-pendaftaran.view.all',

            // User Management
            'users.view',

            // Dashboard Card Permissions
            'dashboard.view',
            'dashboard.view-card.message.self',
            'dashboard.view-card.users',

        ]);

        // OFFICIAL PELATIH
        $official_pelatih = Role::firstOrCreate(['name' => 'official_pelatih', 'guard_name' => 'web'], ['uid' => (string) \Illuminate\Support\Str::uuid()]);
        $official_pelatih->givePermissionTo([
            // PERSONAL DATA
            // My Profile
            'my-profile.view',
            'my-profile.edit',
            'my-profile.delete',
            'my-profile.change-password',

            // Notification Management
            'notifications.view',
            'notifications.delete',

            // DOCUMENT
            'documents.view-sensitive',

            // MASTER DATA & EVENT
            // Master management Lomba
            'master-lomba.view',

            // Master Management Pendaftaran
            'master-pendaftaran.view',
            'master-pendaftaran.create',
            'master-pendaftaran.filter',

            // Master Management Result
            'master-result.view',
            'master-result.detail',

            // Master History Pendaftaran
            'master-history-pendaftaran.view',
            'master-history-pendaftaran.view.self',
            'master-history-pendaftaran.detail',

            // User Management
            'users.view',

            // Dashboard Card Permissions
            'dashboard.view',
            'dashboard.view-card.message.self',
        ]);

        // ATLET: Hanya bisa melihat event dan hasil
        $athlete = Role::firstOrCreate(['name' => 'atlet', 'guard_name' => 'web'], ['uid' => (string) \Illuminate\Support\Str::uuid()]);
        $athlete->givePermissionTo([
            // PERSONAL DATA
            // My Profile
            'my-profile.view',
            'my-profile.edit',
            'my-profile.delete',
            'my-profile.change-password',

            // Notification Management
            'notifications.view',
            'notifications.delete',

            // DOCUMENT
            'documents.view-sensitive',

            // Master Management Pendaftaran
            'master-pendaftaran.view.self',
            'master-pendaftaran.create.self',
            'master-pendaftaran.edit.self',
            'master-pendaftaran.delete.self',

            // Master Management Result
            'master-result.view',
            'master-result.detail.self',
            'master-result.detail.team',

            // Master History Pendaftaran
            'master-history-pendaftaran.view.self',
            'master-history-pendaftaran.detail',

            // Dashboard Card Permissions
            'dashboard.view',
            'dashboard.view-card.finance.self',
            'dashboard.view-card.history.self',
            'dashboard.view-card.message.self',

        ]);
    }
}
