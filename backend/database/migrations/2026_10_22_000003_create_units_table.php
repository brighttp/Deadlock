<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code', 20)->unique();   // tidak didaur ulang walau unit dihapus
            $table->string('name', 100)->index();        // boleh sama, dipakai untuk pencarian
            $table->text('description')->nullable();
            $table->enum('status', ['tersedia', 'dipinjam'])->default('tersedia');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
