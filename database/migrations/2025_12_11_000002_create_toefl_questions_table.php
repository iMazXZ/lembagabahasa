<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toefl_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('toefl_packages')->cascadeOnDelete();
            $table->enum('section', ['listening', 'structure', 'reading']);
            $table->unsignedSmallInteger('question_number');
            $table->text('passage')->nullable(); // Untuk Reading
            $table->text('question');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->char('correct_answer', 1); // A/B/C/D
            $table->timestamps();

            $table->unique(['package_id', 'section', 'question_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toefl_questions');
    }
};
