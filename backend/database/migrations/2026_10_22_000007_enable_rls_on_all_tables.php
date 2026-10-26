<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menutup akses Data API (REST) Supabase ke seluruh tabel.
 * RLS diaktifkan tanpa policy: anon/authenticated tidak bisa membaca apa pun,
 * sedangkan Laravel tetap normal karena terhubung sebagai pemilik tabel (postgres).
 */
return new class extends Migration
{
    private array $tables = [
        'migrations',
        'users',
        'profiles',
        'categories',
        'units',
        'category_unit',
        'loans',
    ];

    public function up(): void
    {
        // RLS hanya ada di PostgreSQL; dilewati saat test memakai SQLite.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
            }
        }
    }
};
