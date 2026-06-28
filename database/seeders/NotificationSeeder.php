<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 10; $i++) {
                Notification::create([
                    'uid' => Str::uuid(),
                    'user_uid' => $user->uid,
                    'title' => 'Notifikasi ' . ($i + 1),
                    'message' => 'Ini adalah isi dari notifikasi ke ' . ($i + 1) . ' untuk user ' . $user->username,
                ]);
            }
        }
    }
}
