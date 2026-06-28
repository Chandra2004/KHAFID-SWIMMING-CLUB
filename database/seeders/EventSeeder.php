<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) return;

        $events = [
            ['name' => 'KSC Cup 2026', 'location' => 'Kolam Renang Senayan'],
            ['name' => 'Piala Walikota Surabaya 2026', 'location' => 'Kolam Renang Manyar'],
            ['name' => 'Kejurda Jatim Seri 1', 'location' => 'KONI Jatim Pool'],
            ['name' => 'Invitasi Renang Antar Perkumpulan', 'location' => 'GOR Delta Sidoarjo'],
            ['name' => 'Swimming Fun Game 2026', 'location' => 'KSC Home Base'],
            ['name' => 'Kejuaraan Renang Master Indonesia', 'location' => 'Cikini Pool Jakarta'],
            ['name' => 'Sprint Swimming Championship', 'location' => 'UNESA Swimming Pool'],
            ['name' => 'Open Water Swimming Festival', 'location' => 'Pantai Kenjeran'],
            ['name' => 'Lomba Renang Pemula Nasional', 'location' => 'GOR Pajajaran Bandung'],
            ['name' => 'Final Series KSC 2026', 'location' => 'Stadion Akuatik GBK'],
        ];

        $financeAccounts = \App\Models\FinanceAccount::all();
        
        foreach ($events as $index => $event) {
            Event::create([
                'uid' => Str::uuid(),
                'name' => $event['name'],
                'slug' => Str::slug($event['name']) . '-' . $index,
                'description' => 'Kejuaraan renang bergengsi yang diselenggarakan oleh KSC untuk menjaring bibit atlet berbakat.',
                'location' => $event['location'],
                'start_date' => now()->addDays(($index + 1) * 7),
                'end_date' => now()->addDays(($index + 1) * 7 + 2),
                'start_time' => '08:00:00',
                'lane_count' => 8,
                'status' => 'ongoing',
                'payment_method_uid' => $financeAccounts->isNotEmpty() ? $financeAccounts->random()->uid : null,
                'author_uid' => $admin->uid,
            ]);
        }
    }
}
