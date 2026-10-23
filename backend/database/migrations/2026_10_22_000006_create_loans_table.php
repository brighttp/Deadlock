<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            // restrict => riwayat peminjaman tidak boleh hilang
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->date('loan_date')->index();          // acuan filter riwayat
            $table->date('due_date');                    // loan_date + 5 hari
            $table->date('return_date')->nullable();     // null selama pinjaman aktif
            $table->integer('fine_amount')->default(0);  // rupiah
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->timestamps();

            $table->index(['user_id', 'status']);        // hitung pinjaman aktif per anggota
            $table->index(['unit_id', 'status']);
        });

        DB::statement('ALTER TABLE loans ADD CONSTRAINT loans_fine_amount_non_negative CHECK (fine_amount >= 0)');
        DB::statement('ALTER TABLE loans ADD CONSTRAINT loans_due_date_after_loan_date CHECK (due_date >= loan_date)');
        DB::statement('ALTER TABLE loans ADD CONSTRAINT loans_return_date_after_loan_date CHECK (return_date IS NULL OR return_date >= loan_date)');
        DB::statement("ALTER TABLE loans ADD CONSTRAINT loans_status_matches_return_date CHECK ((status = 'aktif' AND return_date IS NULL) OR (status = 'selesai' AND return_date IS NOT NULL))");

        // Satu unit hanya boleh punya satu pinjaman aktif (pengaman race condition).
        DB::statement("CREATE UNIQUE INDEX loans_unit_active_unique ON loans (unit_id) WHERE status = 'aktif'");
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
