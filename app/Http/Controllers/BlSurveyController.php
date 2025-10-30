<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningSurvey;
use App\Models\BasicListeningSurveyAnswer;
use App\Models\BasicListeningSurveyResponse;
use App\Models\BasicListeningSupervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BlSurveyController extends Controller
{
    /**
     * Form pemilihan Tutor & Lembaga (Supervisor).
     * GET /bl/survey/start
     */
    public function start(Request $request)
    {
        // Tutor = user dengan role 'tutor'
        $tutors = User::query()
            ->role('tutor')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Lembaga (Supervisor) aktif
        $supervisors = BasicListeningSupervisor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Prefill dari session kalau ada (UX)
        $prefill = [
            'tutor_id'      => (int) $request->session()->get('bl_selected_tutor_id'),
            'supervisor_id' => (int) $request->session()->get('bl_selected_supervisor_id'),
        ];

        return view('bl.survey_start', compact('tutors', 'supervisors', 'prefill'));
    }

    /**
     * Simpan pilihan Tutor & Supervisor ke session.
     * POST /bl/survey/start
     */
    public function startSubmit(Request $request)
    {
        $data = $request->validate([
            'tutor_id'      => ['required', 'integer', Rule::exists('users', 'id')],
            'supervisor_id' => ['required', 'integer', Rule::exists('basic_listening_supervisors', 'id')->where('is_active', true)],
        ]);

        // (Opsional) hard-guard: pastikan id tutor benar-benar ber-role tutor.
        $isTutor = User::query()->role('tutor')->whereKey($data['tutor_id'])->exists();
        abort_unless($isTutor, 422, 'Pilihan tutor tidak valid.');

        $request->session()->put('bl_selected_tutor_id', (int) $data['tutor_id']);
        $request->session()->put('bl_selected_supervisor_id', (int) $data['supervisor_id']);

        return redirect()
            ->route('bl.survey.required')
            ->with('success', 'Pilihan tersimpan. Silakan lanjut mengisi kuesioner.');
    }

    /**
     * Arahkan user ke survey wajib berikutnya (chaining):
     * tutor → supervisor → institute.
     */
    public function redirectToRequired(Request $request)
    {
        $userId = (int) auth()->id();

        if ($survey = $this->nextPendingSurveyFor($userId)) {
            // Pastikan pilihan awal tersedia untuk kategori terkait
            if ($survey->category === 'tutor' && ! $request->session()->get('bl_selected_tutor_id')) {
                return redirect()->route('bl.survey.start')->with('info', 'Pilih tutor & lembaga terlebih dahulu.');
            }
            if ($survey->category === 'supervisor' && ! $request->session()->get('bl_selected_supervisor_id')) {
                return redirect()->route('bl.survey.start')->with('info', 'Pilih tutor & lembaga terlebih dahulu.');
            }

            return redirect()->route('bl.survey.show', $survey);
        }

        // Semua selesai
        $target = app('router')->has('bl.history') ? 'bl.history' : 'bl.index';
        return redirect()->route($target)->with('success', 'Terima kasih! Semua kuesioner telah diisi.');
    }

    /**
     * Tampilkan halaman survey.
     * - Buat/ambil draft response (unik per user + survey + session).
     * - Auto-set tutor/supervisor dari session saat pertama kali.
     */
    public function show(Request $request, BasicListeningSurvey $survey)
    {
        abort_unless($survey->isOpen(), 403, 'Kuesioner belum/tidak tersedia.');

        // Scope session_id bila target = session
        $sessionId = null;
        if ($survey->target === 'session') {
            $sessionId = (int) $request->query('session_id', $survey->session_id);
        }

        // Draft response unik
        $response = BasicListeningSurveyResponse::firstOrCreate([
            'survey_id'  => $survey->id,
            'user_id'    => (int) auth()->id(),
            'session_id' => $sessionId,
        ]);

        // Auto-isi foreign key sesuai kategori jika kosong
        $tutorIdFromSession      = (int) $request->session()->get('bl_selected_tutor_id');
        $supervisorIdFromSession = (int) $request->session()->get('bl_selected_supervisor_id');

        $dirty = false;

        if ($survey->category === 'tutor' && empty($response->tutor_id) && $tutorIdFromSession) {
            $response->tutor_id = $tutorIdFromSession;
            $dirty = true;
        }

        if ($survey->category === 'supervisor' && empty($response->supervisor_id) && $supervisorIdFromSession) {
            $response->supervisor_id = $supervisorIdFromSession;
            $dirty = true;
        }

        if ($dirty) {
            $response->save();
        }

        $survey->load('questions');

        return view('bl.survey_show', [
            'survey'   => $survey,
            'response' => $response,
        ]);
    }

    /**
     * Submit jawaban survey; lalu arahkan ke survey berikutnya (jika ada).
     * POST /bl/survey/{survey}
     */
    public function submit(Request $request, BasicListeningSurvey $survey)
    {
        abort_unless($survey->isOpen(), 403, 'Kuesioner sudah tidak tersedia.');

        $sessionId = null;
        if ($survey->target === 'session') {
            $sessionId = (int) $request->input('session_id', $survey->session_id);
        }

        // Ambil / buat draft response
        $response = BasicListeningSurveyResponse::firstOrCreate([
            'survey_id'  => $survey->id,
            'user_id'    => (int) auth()->id(),
            'session_id' => $sessionId,
        ]);

        // Pastikan tutor/supervisor terisi sesuai kategori
        if ($survey->category === 'tutor' && empty($response->tutor_id)) {
            $tid = (int) $request->session()->get('bl_selected_tutor_id');
            abort_if(! $tid, 422, 'Silakan pilih tutor di halaman awal kuesioner.');
            $response->tutor_id = $tid;
        }

        if ($survey->category === 'supervisor' && empty($response->supervisor_id)) {
            $sid = (int) $request->session()->get('bl_selected_supervisor_id');
            abort_if(! $sid, 422, 'Silakan pilih lembaga di halaman awal kuesioner.');
            $response->supervisor_id = $sid;
        }

        $response->save();

        // Validasi dinamis berdasarkan tipe pertanyaan
        $survey->load('questions');
        $rules = [];
        foreach ($survey->questions as $q) {
            $key = "q.{$q->id}";
            if ($q->type === 'likert') {
                $rules[$key] = $q->is_required ? 'required|integer|between:1,5' : 'nullable|integer|between:1,5';
            } else {
                $rules[$key] = $q->is_required ? 'required|string|max:2000' : 'nullable|string|max:2000';
            }
        }

        $validated = $request->validate($rules);

        // Simpan jawaban & tandai submitted
        DB::transaction(function () use ($survey, $response, $validated) {
            foreach ($survey->questions as $q) {
                $value = $validated['q'][$q->id] ?? null;

                BasicListeningSurveyAnswer::updateOrCreate(
                    [
                        'response_id' => $response->id,
                        'question_id' => $q->id,
                    ],
                    $q->type === 'likert'
                        ? ['likert_value' => $value, 'text_value' => null]
                        : ['likert_value' => null, 'text_value' => $value]
                );
            }

            $response->forceFill(['submitted_at' => now()])->save();
        });

        // Arahkan ke survey berikutnya (kalau masih ada yang pending)
        if ($next = $this->nextPendingSurveyFor((int) auth()->id())) {
            return redirect()
                ->route('bl.survey.show', $next)
                ->with('success', 'Kuesioner tersimpan. Lanjutkan kuesioner berikutnya.');
        }

        // Semua selesai
        $targetRoute = app('router')->has('bl.history') ? 'bl.history' : 'bl.index';
        return redirect()
            ->route($targetRoute)
            ->with('success', 'Terima kasih! Semua kuesioner selesai.');
    }

    /**
     * Cari survey wajib berikutnya yang masih pending bagi user.
     * Urutan: tutor → supervisor → institute. Hanya target 'final'.
     */
    private function nextPendingSurveyFor(int $userId): ?BasicListeningSurvey
    {
        $categories = ['tutor', 'supervisor', 'institute'];

        foreach ($categories as $cat) {
            $survey = BasicListeningSurvey::query()
                ->where('require_for_certificate', true)
                ->where('target', 'final')
                ->where('category', $cat)
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if (! $survey || ! $survey->isOpen()) {
                continue;
            }

            $alreadySubmitted = BasicListeningSurveyResponse::query()
                ->where('survey_id', $survey->id)
                ->where('user_id', $userId)
                ->whereNull('session_id') // final
                ->whereNotNull('submitted_at')
                ->exists();

            if (! $alreadySubmitted) {
                return $survey;
            }
        }

        return null;
    }

    /**
     * (Opsional) Reset pilihan Tutor/Supervisor di session.
     * GET /bl/survey/reset-choice
     */
    public function resetChoice(Request $request)
    {
        $request->session()->forget(['bl_selected_tutor_id', 'bl_selected_supervisor_id']);
        return redirect()->route('bl.survey.start')->with('success', 'Pilihan telah direset.');
    }
}
