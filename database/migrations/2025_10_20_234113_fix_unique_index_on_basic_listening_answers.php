<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 0) Matikan FK checks sementara (agar boleh drop index yang sedang direferensikan)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1) Pastikan kolom blank_index ada & NOT NULL (default '')
        if (!Schema::hasColumn('basic_listening_answers', 'blank_index')) {
            Schema::table('basic_listening_answers', function (Blueprint $t) {
                $t->string('blank_index')->default('')->nullable(false)->after('question_id');
            });
        } else {
            // backfill null -> '' lalu ubah jadi NOT NULL
            DB::table('basic_listening_answers')->whereNull('blank_index')->update(['blank_index' => '']);
            Schema::table('basic_listening_answers', function (Blueprint $t) {
                $t->string('blank_index')->default('')->nullable(false)->change();
            });
        }

        // 2) Hapus unique lama (attempt_id, question_id)
        Schema::table('basic_listening_answers', function (Blueprint $t) {
            // pakai nama konvensi Laravel
            $t->dropUnique('basic_listening_answers_attempt_id_question_id_unique');
            // kalau di DB namanya beda, uncomment salah satu:
            // $t->dropUnique('attempt_id_question_id_unique');
            // $t->dropUnique(['attempt_id','question_id']);
        });

        // 3) Buat unique baru: (attempt_id, question_id, blank_index)
        Schema::table('basic_listening_answers', function (Blueprint $t) {
            $t->unique(['attempt_id','question_id','blank_index'], 'answers_attempt_question_blank_unique');
        });

        // 4) Nyalakan kembali FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('basic_listening_answers', function (Blueprint $t) {
            $t->dropUnique('answers_attempt_question_blank_unique');
            // kembalikan unique lama bila perlu:
            $t->unique(['attempt_id','question_id'], 'basic_listening_answers_attempt_id_question_id_unique');
        });

        // (opsional) kembalikan blank_index jadi nullable
        Schema::table('basic_listening_answers', function (Blueprint $t) {
            $t->string('blank_index')->nullable()->default(null)->change();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
