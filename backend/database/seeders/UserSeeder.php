<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Komandan Marcus Vance', 'email' => 'admin@deadlock.haven', 'role' => User::ROLE_ADMIN,
                'phone' => '081199887711', 'address' => 'Bunker Pusat Komando Sektor A'],
            ['name' => 'Sarah Connor', 'email' => 'sarah@haven.org', 'role' => User::ROLE_ANGGOTA,
                'phone' => '081233445566', 'address' => 'Barak Survivor Blok 4B'],
            ['name' => 'Joel Miller', 'email' => 'joel@haven.org', 'role' => User::ROLE_ANGGOTA,
                'phone' => '081388991122', 'address' => 'Pos Pantau Tembok Utara'],
            ['name' => 'Ellie Williams', 'email' => 'ellie@haven.org', 'role' => User::ROLE_ANGGOTA,
                'phone' => '081577889900', 'address' => 'Barak Survivor Blok 2A'],
            ['name' => 'Glenn Rhee', 'email' => 'glenn@haven.org', 'role' => User::ROLE_ANGGOTA,
                'phone' => '081844556677', 'address' => 'Gudang Distribusi Logistik'],
        ];

        foreach ($users as $data) {
            // User dan profil dibuat dalam satu transaksi: tiap user dijamin tepat satu profil.
            DB::transaction(function () use ($data) {
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'role' => $data['role'], 'password' => 'password123'],
                );

                $user->profile()->updateOrCreate([], [
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ]);
            });
        }
    }
}
