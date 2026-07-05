<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DataUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first club or create a default one to associate
        $club = \App\Models\Club::first();
        if (!$club) {
            $club = \App\Models\Club::create([
                'uid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Khafid Swimming Club',
                'short_name' => 'KSC',
                'coach_name' => 'Coach Khafid',
                'contact' => '085745000468',
                'address' => 'Sidoarjo',
            ]);
        }

        // 1. Create Superadmin
        $superadmin = User::updateOrCreate(
            ['email' => 'chandratriantomo123@gmail.com'],
            [
                'username' => 'superadmin',
                'password' => Hash::make('chandraSA28092004'),
                'is_active' => true,
            ]
        );
        $superadmin->assignRole('superadmin');
        $this->createProfile($superadmin, 'Super Admin', 'male', $club->uid);

        // 2. Create Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@khafid.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('admin28092004'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');
        $this->createProfile($admin, 'Admin Official', 'male', $club->uid);

        // 3. Create Pelatih
        $coach = User::updateOrCreate(
            ['email' => 'pelatih@khafid.com'],
            [
                'username' => 'pelatih',
                'password' => Hash::make('pelatih28092004'),
                'is_active' => true,
            ]
        );
        $coach->assignRole('pelatih');
        $this->createProfile($coach, 'Coach Khafid', 'male', $club->uid);

        // 4. Create Atlet
        $athlete = User::updateOrCreate(
            ['email' => 'atlet@khafid.com'],
            [
                'username' => 'atlet',
                'password' => Hash::make('atlet28092004'),
                'is_active' => true,
            ]
        );
        $athlete->assignRole('atlet');
        $this->createProfile($athlete, 'Atlet Utama KSC', 'male', $club->uid, '2015-06-15');

        // 5. Create Sample Athletes (Birth year 2015)
        // $clubs = \App\Models\Club::all();
        // if ($clubs->isEmpty()) {
        //     $clubs = collect([$club]);
        // }
        // for ($i = 1; $i <= 20; $i++) {
        //     $gender = $i % 2 == 0 ? 'female' : 'male';
        //     $sampleAthlete = User::updateOrCreate(
        //         ['email' => 'atlet' . $i . '@example.com'],
        //         [
        //             'username' => 'atlet_' . $i,
        //             'password' => Hash::make('password123'),
        //             'is_active' => true,
        //         ]
        //     );
        //     $sampleAthlete->assignRole('atlet');

        //     DataUser::updateOrCreate(
        //         ['user_uid' => $sampleAthlete->uid],
        //         [
        //             'uid' => (string) \Illuminate\Support\Str::uuid(),
        //             'full_name' => ($gender === 'female' ? 'Siti ' : 'Rafi ') . 'Athlete ' . $i,
        //             'nickname' => 'atlet' . $i,
        //             'gender' => $gender,
        //             'birth_place' => 'Sidoarjo',
        //             'birth_date' => '2015-08-12',
        //             'phone_number' => '0812345678' . $i,
        //             'backup_phone_number' => '0898765432' . $i,
        //             'address' => 'Jl. Pahlawan No. ' . $i . ', Sidoarjo',
        //             'height' => 145 + $i,
        //             'weight' => 38 + $i,
        //             'identity_number' => '35150000000000' . sprintf('%02d', $i),
        //             'medical_history' => 'Sehat Walafiat',
        //             'club_uid' => $clubs->random()->uid,
        //             'is_active' => true,
        //             'joined_at' => now(),
        //         ]
        //     );
        // }
    }

    /**
     * Helper to create a basic profile
     */
    private function createProfile($user, $name, $gender = 'male', $clubUid = null, $birthDate = '2015-01-01')
    {
        DataUser::updateOrCreate(
            ['user_uid' => $user->uid],
            [
                'uid' => (string) \Illuminate\Support\Str::uuid(),
                'full_name' => $name,
                'nickname' => strtolower(explode(' ', $name)[0]),
                'gender' => $gender,
                'birth_place' => 'Sidoarjo',
                'birth_date' => $birthDate,
                'phone_number' => '081234567890',
                'backup_phone_number' => '089876543210',
                'address' => 'Jl. Raya Swimming Club No. 28, Sidoarjo',
                'height' => 150,
                'weight' => 45,
                'identity_number' => '3515012345678901',
                'medical_history' => 'Tidak ada',
                'club_uid' => $clubUid,
                'is_active' => true,
                'joined_at' => now(),
            ]
        );
    }
}
