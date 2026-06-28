<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            ['name' => 'Gaya Bebas', 'description' => 'Gaya renang dengan posisi dada menghadap ke permukaan air.'],
            ['name' => 'Gaya Dada', 'description' => 'Gaya katak, posisi dada menghadap ke permukaan air.'],
            ['name' => 'Gaya Punggung', 'description' => 'Gaya renang dengan posisi punggung menghadap ke permukaan air.'],
            ['name' => 'Gaya Kupu-kupu', 'description' => 'Gaya renang dengan kedua lengan secara bersamaan dikayuh ke depan.'],
            ['name' => 'Gaya Ganti Perorangan', 'description' => 'Kombinasi empat gaya renang dalam satu nomor.'],
            ['name' => 'Gaya Ganti Estafet', 'description' => 'Kombinasi empat gaya renang oleh empat perenang.'],
            ['name' => 'Kicking Bebas', 'description' => 'Latihan kaki gaya bebas menggunakan papan pelampung.'],
            ['name' => 'Kicking Dada', 'description' => 'Latihan kaki gaya dada menggunakan papan pelampung.'],
            ['name' => 'Finswimming', 'description' => 'Renang menggunakan kaki katak (fins).'],
            ['name' => 'Open Water Swimming', 'description' => 'Renang di perairan terbuka.'],
        ];

        foreach ($styles as $style) {
            Category::updateOrCreate(['name' => $style['name']], [
                'uid' => Str::uuid(),
                'slug' => Str::slug($style['name']),
                'description' => $style['description'],
                'is_active' => true,
            ]);
        }
    }
}
