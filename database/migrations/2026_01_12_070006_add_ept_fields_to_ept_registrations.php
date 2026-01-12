<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ept_registrations', function (Blueprint $table) {
            // Session assignment
            $table->foreignId('session_id')->nullable()->after('user_id')->constrained('ept_sessions')->nullOnDelete();
            
            // Token CBT (generated, muncul setelah diabsen pengawas)
            $table->string('cbt_token')->nullable()->after('status');
            $table->timestamp('token_released_at')->nullable()->after('cbt_token');
            
            // Swafoto saat daftar atau mulai ujian
            $table->string('selfie_path')->nullable()->after('token_released_at');
        });
    }

    public function down(): void
    {
        Schema::table('ept_registrations', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropColumn(['session_id', 'cbt_token', 'token_released_at', 'selfie_path']);
        });
    }
};
