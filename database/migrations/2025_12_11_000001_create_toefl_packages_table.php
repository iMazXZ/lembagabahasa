<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toefl_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Paket A", "Paket B", dll
            $table->string('listening_audio_path')->nullable(); // 1 file untuk 50 soal
            $table->unsignedInteger('listening_duration')->default(35); // menit
            $table->unsignedInteger('structure_duration')->default(25); // menit
            $table->unsignedInteger('reading_duration')->default(55); // menit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toefl_packages');
    }
};
