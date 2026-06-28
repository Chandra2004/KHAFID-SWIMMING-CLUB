<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\User;
use App\Models\EventCategory;
use App\Models\Result;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::role('atlet')->get();
        $categories = EventCategory::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Create 3 registrations for each athlete
            $selectedCats = $categories->random(min(3, $categories->count()));

            foreach ($selectedCats as $cat) {
                $status = collect(['confirmed', 'confirmed', 'confirmed', 'pending', 'cancelled', 'rejected'])->random();
                $notes = 'Sampel pendaftaran otomatis dari sistem seeder.';
                
                if ($status === 'rejected') {
                    $notes = collect([
                        'Bukti pembayaran tidak terbaca atau buram.',
                        'Usia atlet tidak sesuai dengan kategori lomba yang dipilih.',
                        'Klub belum melunasi iuran tahunan organisasi.',
                        'Data identitas atlet (Akte/KK) tidak valid.',
                        'Pendaftaran melebihi kuota maksimal event.'
                    ])->random();
                } elseif ($status === 'cancelled') {
                    $notes = collect([
                        'Atlet mengundurkan diri karena alasan kesehatan.',
                        'Jadwal bentrok dengan kegiatan sekolah.',
                        'Kesalahan pemilihan kategori lomba oleh wali atlet.',
                    ])->random();
                }

                $reg = Registration::create([
                    'uid' => (string) Str::uuid(),
                    'user_uid' => $user->uid,
                    'event_category_uid' => $cat->uid,
                    'registration_number' => 'REG-' . strtoupper(Str::random(6)),
                    'status' => $status,
                    'notes' => $notes,
                    'entry_time' => now()->subDays(rand(1, 30)),
                ]);

                // If confirmed, add payment and result
                if ($status === 'confirmed') {
                    Payment::create([
                        'uid' => (string) Str::uuid(),
                        'registration_uid' => $reg->uid,
                        'amount' => $cat->registration_fee ?: 0,
                        'method' => 'cash',
                        'status' => 'paid',
                        'paid_at' => now()->subDays(rand(1, 5)),
                    ]);

                    \App\Models\Schedule::create([
                        'uid' => (string) \Illuminate\Support\Str::uuid(),
                        'registration_uid' => $reg->uid,
                        'heat_number' => rand(1, 10),
                        'lane_number' => rand(0, 7),
                    ]);

                    Result::create([
                        'uid' => (string) Str::uuid(),
                        'registration_uid' => $reg->uid,
                        'final_time' => sprintf('%02d:%02d.%02d', rand(0, 2), rand(0, 59), rand(0, 99)),
                        'rank' => rand(1, 10),
                        'status' => 'FINISH',
                        'official_name' => 'Seeder Official',
                    ]);
                }
            }
        }
    }
}
