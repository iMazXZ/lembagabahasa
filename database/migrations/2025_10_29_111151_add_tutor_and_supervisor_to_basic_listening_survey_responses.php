<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('basic_listening_survey_responses', function (Blueprint $table) {
            // ——— TUTOR (ke tabel users)
            if (! Schema::hasColumn('basic_listening_survey_responses', 'tutor_id')) {
                $table->foreignId('tutor_id')->nullable()->after('user_id');
            } else {
                // pastikan tipe/constraint aman (drop FK lama jika ada)
                try { $table->dropForeign(['tutor_id']); } catch (\Throwable $e) {}
            }
            $table->foreign('tutor_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            // ——— SUPERVISOR (ke tabel basic_listening_supervisors)
            if (! Schema::hasColumn('basic_listening_survey_responses', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->nullable()->after('tutor_id');
            } else {
                try { $table->dropForeign(['supervisor_id']); } catch (\Throwable $e) {}
            }
            $table->foreign('supervisor_id')
                ->references('id')->on('basic_listening_supervisors')
                ->nullOnDelete();

            // ——— META opsional untuk catatan/ruang dsb
            if (! Schema::hasColumn('basic_listening_survey_responses', 'meta')) {
                $table->json('meta')->nullable()->after('submitted_at');
            }

            // Index bantu (opsional, bagus untuk agregasi)
            $table->index(['survey_id', 'tutor_id']);
            $table->index(['survey_id', 'supervisor_id']);
        });
    }

    public function down(): void
    {
        Schema::table('basic_listening_survey_responses', function (Blueprint $table) {
            try { $table->dropForeign(['tutor_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['supervisor_id']); } catch (\Throwable $e) {}

            if (Schema::hasColumn('basic_listening_survey_responses', 'tutor_id')) {
                $table->dropColumn('tutor_id');
            }
            if (Schema::hasColumn('basic_listening_survey_responses', 'supervisor_id')) {
                $table->dropColumn('supervisor_id');
            }
            if (Schema::hasColumn('basic_listening_survey_responses', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
