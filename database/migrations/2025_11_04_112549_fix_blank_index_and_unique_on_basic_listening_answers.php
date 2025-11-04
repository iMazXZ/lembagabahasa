<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('basic_listening_answers', function (Blueprint $table) {
            // ubah tipe blank_index -> unsigned smallint
            $table->unsignedSmallInteger('blank_index')->nullable(false)->change();

            // hapus unique lama
            $table->dropUnique('answers_attempt_question_blank_unique');

            // buat unique baru lengkap
            $table->unique(['attempt_id', 'question_id', 'blank_index'], 'bla_attempt_question_blank_unique');
        });
    }

    public function down(): void
    {
        Schema::table('basic_listening_answers', function (Blueprint $table) {
            $table->dropUnique('bla_attempt_question_blank_unique');
            $table->unique(['question_id', 'blank_index'], 'answers_attempt_question_blank_unique');
            $table->string('blank_index', 255)->change();
        });
    }
};
