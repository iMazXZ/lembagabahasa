<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ept_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('ept_registrations')->nullOnDelete();
            $table->foreignId('quiz_id')->constrained('ept_quizzes')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('ept_sessions')->nullOnDelete();
            
            // Waktu ujian
            $table->timestamp('started_at')->nullable();
            $table->timestamp('listening_started_at')->nullable();
            $table->timestamp('structure_started_at')->nullable();
            $table->timestamp('reading_started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            
            // Status section saat ini
            $table->enum('current_section', ['listening', 'structure', 'reading'])->default('listening');
            
            // Skor per section (raw score)
            $table->unsignedSmallInteger('score_listening')->nullable();
            $table->unsignedSmallInteger('score_structure')->nullable();
            $table->unsignedSmallInteger('score_reading')->nullable();
            
            // Skor scaled (TOEFL scale 31-68 per section)
            $table->unsignedSmallInteger('scaled_listening')->nullable();
            $table->unsignedSmallInteger('scaled_structure')->nullable();
            $table->unsignedSmallInteger('scaled_reading')->nullable();
            
            // Total skor (TOEFL scale: 3 section × 10 = 310-677)
            $table->unsignedSmallInteger('total_score')->nullable();
            
            // Swafoto saat mulai ujian
            $table->string('selfie_path')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ept_attempts');
    }
};
