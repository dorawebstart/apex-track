<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Класи авто для трек-днів.
     */
    public function run(): void
    {
        foreach ([
            'supercars' => 'Суперкари',
            'grand-tourer' => 'Гран Турізмо',
            'hypercars' => 'Гіперкари',
            'track-only' => 'Трекові',
        ] as $slug => $name) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
