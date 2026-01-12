<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ept_quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // EPT Januari 2026
            $table->text('description')->nullable();
            
            // Durasi per section (menit)
            $table->unsignedSmallInteger('listening_duration')->default(35);
            $table->unsignedSmallInteger('structure_duration')->default(25);
            $table->unsignedSmallInteger('reading_duration')->default(55);
            
            // Jumlah soal per section (untuk validasi)
            $table->unsignedSmallInteger('listening_count')->default(50);
            $table->unsignedSmallInteger('structure_count')->default(40);
            $table->unsignedSmallInteger('reading_count')->default(50);
            
            // Scoring config (JSON untuk flexibility)
            $table->json('scoring_config')->nullable()->comment('Custom scoring formula');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ept_quizzes');
    }
};
