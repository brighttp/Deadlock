<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('return_status')->nullable()->after('status'); // null, 'diajukan', 'ditolak'
            $table->timestamp('return_requested_at')->nullable()->after('return_status');
            $table->string('return_rejection_note')->nullable()->after('return_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['return_status', 'return_requested_at', 'return_rejection_note']);
        });
    }
};
