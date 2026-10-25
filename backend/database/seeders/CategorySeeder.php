<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Senjata Jarak Dekat',
            'Senjata Jarak Jauh',
            'Peralatan Survival',
            'Medis',
            'Komunikasi',
            'Kendaraan',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
