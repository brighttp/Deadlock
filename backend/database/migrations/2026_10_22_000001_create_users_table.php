<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                 // nama lengkap
            $table->string('email', 100);                // disimpan huruf kecil
            $table->string('password');                  // hash
            $table->enum('role', ['admin', 'anggota'])->default('anggota');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Email unik (tidak membedakan huruf besar/kecil) hanya untuk user yang belum dihapus,
        // sehingga email milik user yang sudah di-soft delete bisa dipakai lagi.
        DB::statement('CREATE UNIQUE INDEX users_email_active_unique ON users (LOWER(email)) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
