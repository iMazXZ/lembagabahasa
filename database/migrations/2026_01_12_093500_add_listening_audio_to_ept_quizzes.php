<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ept_quizzes', function (Blueprint $table) {
            $table->string('listening_audio_url')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('ept_quizzes', function (Blueprint $table) {
            $table->dropColumn('listening_audio_url');
        });
    }
};
