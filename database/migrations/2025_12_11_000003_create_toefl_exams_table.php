<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toefl_exams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "EPT Gelombang 1 - Januari 2025"
            $table->foreignId('package_id')->constrained('toefl_packages');
            $table->dateTime('scheduled_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toefl_exams');
    }
};
