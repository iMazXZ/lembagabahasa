<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ept_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('ept_quizzes')->cascadeOnDelete();
            
            // Section: listening, structure, reading
            $table->enum('section', ['listening', 'structure', 'reading']);
            $table->unsignedSmallInteger('order')->default(1);
            
            // Soal
            $table->text('question')->nullable(); // Bisa kosong untuk listening
            $table->string('audio_url')->nullable(); // Untuk listening
            $table->text('passage')->nullable(); // Untuk reading passage
            
            // Pilihan jawaban
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            
            // Jawaban benar
            $table->char('correct_answer', 1); // A/B/C/D
            
            // Grouping untuk reading (soal 1-10 pakai passage yang sama)
            $table->unsignedInteger('passage_group')->nullable();
            
            $table->timestamps();
            
            $table->index(['quiz_id', 'section', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ept_questions');
    }
};
