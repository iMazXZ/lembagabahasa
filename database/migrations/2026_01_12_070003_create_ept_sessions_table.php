<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ept_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('ept_quizzes')->cascadeOnDelete();
            
            $table->string('name'); // Sesi 1 - Pagi
            $table->date('date'); // Tanggal ujian
            $table->time('start_time'); // 08:00
            $table->time('end_time'); // 11:00
            
            $table->unsignedSmallInteger('max_participants')->default(20);
            $table->string('passcode')->nullable(); // Kode dari pengawas
            
            // Zoom proctoring
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_passcode')->nullable();
            $table->string('zoom_link')->nullable();
            
            // Mode: online atau offline
            $table->enum('mode', ['online', 'offline'])->default('offline');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['date', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ept_sessions');
    }
};
