<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Category;
use App\Models\EventCategory;
use App\Models\RequirementParameter;
use Illuminate\Support\Str;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::all();
        $categories = Category::all();
        $params = RequirementParameter::all();

        if ($events->isEmpty() || $categories->isEmpty()) return;

        $lombaNames = [
            '50M GAYA BEBAS PUTRA',
            '50M GAYA BEBAS PUTRI',
            '100M GAYA DADA PUTRA',
            '100M GAYA DADA PUTRI',
            '50M GAYA KUPU-KUPU PUTRA',
            '50M GAYA PUNGGUNG PUTRI',
            '200M GAYA GANTI PERORANGAN',
            '4X50M ESTAFET GAYA BEBAS',
            '25M KICKING BEBAS PEMULA',
            '50M FINSWIMMING MIXED',
        ];

        foreach ($events as $event) {
            foreach ($lombaNames as $index => $name) {
                $category = $categories->random();
                $param = $params->where('parameter_key', 'birth_year')->first();

                EventCategory::create([
                    'uid' => Str::uuid(),
                    'event_uid' => $event->uid,
                    'category_uid' => $category->uid,
                    'acara_number' => 100 + $index + 1,
                    'acara_name' => $name,
                    'parameter_uid' => $param?->uid,
                    'operator' => '=',
                    'parameter_value' => '2015',
                    'main_requirement' => 'KU 2015-2016 (MINIMAL USIA 10 TAHUN)',
                    'type' => $index % 2 == 0 ? 'paid' : 'free',
                    'registration_fee' => $index % 2 == 0 ? 50000 : 0,
                    'total_series' => rand(1, 4),
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'start_time' => $event->start_time,
                    'end_time' => $event->start_time ? date('H:i:s', strtotime($event->start_time) + 3600) : null,
                    'location' => $event->location,
                ]);
            }
        }
    }
}
