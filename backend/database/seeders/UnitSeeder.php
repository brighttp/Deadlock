<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // UNIT-011 & UNIT-012 sengaja bernama sama dengan unit lain (dibedakan kode unit).
        $units = [
            ['UNIT-001', 'Machete Taktis Carbon', 'Baja karbon tinggi dengan pegangan ergonomis anti-selip untuk pertahanan jarak dekat.', ['Senjata Jarak Dekat', 'Peralatan Survival']],
            ['UNIT-002', 'Baseball Bat Bertulang Baja', 'Tongkat pemukul kayu padat diperkuat plat baja dan kawat berduri.', ['Senjata Jarak Dekat']],
            ['UNIT-003', 'Tactical Crossbow Silent-X', 'Busur silang berdaya tembak 185 lbs dengan peredam suara getaran.', ['Senjata Jarak Jauh']],
            ['UNIT-004', 'Busur Recurve Hunter', 'Busur panah tradisional 45 lbs lengkap dengan tabung 12 anak panah berujung broadhead.', ['Senjata Jarak Jauh', 'Peralatan Survival']],
            ['UNIT-005', 'Emergency Trauma Kit P3K', 'Paket pertolongan luka parah: tourniquet militer, perban hemostatik, antiseptik, dan jarum jahitan steril.', ['Medis', 'Peralatan Survival']],
            ['UNIT-006', 'Walkie-Talkie Frekuensi Militer', 'Radio dua arah tahan cuaca ekstrem dengan jangkauan 15 km saluran terenkripsi.', ['Komunikasi', 'Peralatan Survival']],
            ['UNIT-007', 'Genset Portabel Senyap 2000W', 'Generator listrik bahan bakar ganda dengan isolasi suara rendah untuk suplai bunker.', ['Peralatan Survival']],
            ['UNIT-008', 'Senter Taktis 2000 Lumens', 'Senter tahan air dengan mode strobe pertahanan dan baterai rechargeable surya.', ['Peralatan Survival', 'Komunikasi']],
            ['UNIT-009', 'Motor Trail Pengintai 150cc', 'Kendaraan roda dua tangguh medan lumpur dan puing perkotaan karantina.', ['Kendaraan']],
            ['UNIT-010', 'Pisau Komando Survival M9', 'Pisau belati bergerigi dengan kompas terintegrasi dan kawat pemotong pada sarung.', ['Senjata Jarak Dekat', 'Peralatan Survival']],
            ['UNIT-011', 'Machete Taktis Carbon', 'Unit cadangan, sarung kulit cokelat.', ['Senjata Jarak Dekat']],
            ['UNIT-012', 'Emergency Trauma Kit P3K', 'Unit cadangan untuk pos pantau utara.', ['Medis']],
        ];

        $categoryIds = Category::pluck('id', 'name');

        foreach ($units as [$code, $name, $description, $categories]) {
            $unit = Unit::updateOrCreate(
                ['unit_code' => $code],
                ['name' => $name, 'description' => $description],
            );

            $unit->categories()->sync($categoryIds->only($categories)->values());
        }
    }
}
