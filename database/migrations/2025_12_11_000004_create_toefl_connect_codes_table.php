<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toefl_connect_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('toefl_exams')->cascadeOnDelete();
            $table->string('code_hash', 128); // sha256 hex
            $table->string('code_hint')->nullable(); // "Grup 001"
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('max_uses')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['exam_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toefl_connect_codes');
    }
};
