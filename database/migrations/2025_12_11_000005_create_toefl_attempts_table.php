<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toefl_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('toefl_exams')->cascadeOnDelete();
            $table->foreignId('connect_code_id')->constrained('toefl_connect_codes');

            // Waktu per section
            $table->dateTime('listening_started_at')->nullable();
            $table->dateTime('listening_ended_at')->nullable();
            $table->dateTime('structure_started_at')->nullable();
            $table->dateTime('structure_ended_at')->nullable();
            $table->dateTime('reading_started_at')->nullable();
            $table->dateTime('reading_ended_at')->nullable();
            $table->dateTime('submitted_at')->nullable();

            // Skor mentah (jumlah benar)
            $table->unsignedSmallInteger('listening_correct')->nullable();
            $table->unsignedSmallInteger('structure_correct')->nullable();
            $table->unsignedSmallInteger('reading_correct')->nullable();

            // Skor konversi TOEFL
            $table->unsignedSmallInteger('listening_score')->nullable();
            $table->unsignedSmallInteger('structure_score')->nullable();
            $table->unsignedSmallInteger('reading_score')->nullable();
            $table->unsignedSmallInteger('total_score')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toefl_attempts');
    }
};
