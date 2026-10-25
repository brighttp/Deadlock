<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_unit', function (Blueprint $table) {
            // restrict => kategori yang masih dipakai unit tidak bisa dihapus
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();

            $table->primary(['category_id', 'unit_id']); // mencegah duplikat
            $table->index('unit_id');                    // Postgres tidak otomatis meng-index FK
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_unit');
    }
};
