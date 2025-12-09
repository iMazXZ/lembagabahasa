<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BasicListeningGrade;
use App\Models\Penerjemahan;
use App\Support\ImageTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TranslationController extends Controller
{
    /* =================== Helpers sama seperti di ListPenerjemahans =================== */

    protected function userHasCompleteBiodata(): bool
    {
        $u = Auth::user();
        if (! $u) return false;

        // catatan: di ListPenerjemahans pakai $u->prody (relasi),
        // di dashboard kita aman pakai prody_id juga
        $hasBasicInfo = !empty($u->prody) && !empty($u->srn) && !empty($u->year);
        if (! $hasBasicInfo) return false;

        $year = (int) $u->year;
        $isS2 = $u->prody && str_starts_with($u->prody->name ?? '', 'S2');

        // S2 tidak perlu nilai BL
        if ($isS2) {
            return true;
        }

        if ($year <= 2024) {
            // angkatan lama: wajib nilai BL manual
            return is_numeric($u->nilaibasiclistening);
        }

        // 2025+ biodata dasar saja sudah cukup (cek BL di helper lain)
        return true;
    }

    protected function userHasCompletedBasicListening(): bool
    {
        $u = Auth::user();
        if (! $u) return false;

        // S2 tidak perlu Basic Listening
        $isS2 = $u->prody && str_starts_with($u->prody->name ?? '', 'S2');
        if ($isS2) {
            return true;
        }

        $year = (int) $u->year;

        if ($year < 2025) {
            // 2024 kebawah tidak relevan di sini (pakai nilai manual)
            return true;
        }

        $grade = BasicListeningGrade::query()
            ->where('user_id', $u->id)
            ->where('user_year', $u->year)
            ->first();

        return $grade !== null
            && is_numeric($grade->attendance)
            && is_numeric($grade->final_test);
    }

    /* ============================= INDEX =================================== */

    public function index(Request $request)
    {
        $user = $request->user();

        $biodataComplete = $this->userHasCompleteBiodata();
        $completedBL     = $this->userHasCompletedBasicListening();

        // daftar permohonan milik user
        $records = Penerjemahan::query()
            ->where('user_id', $user->id)
            ->orderByDesc('submission_date')
            ->get();

        // boleh buat permintaan baru?
        $canCreate = $biodataComplete && $completedBL;

        return view('dashboard.translation.index', [
            'user'            => $user,
            'records'         => $records,
            'biodataComplete' => $biodataComplete,
            'completedBL'     => $completedBL,
            'canCreate'       => $canCreate,
        ]);
    }

    /* ============================= CREATE =================================== */

    public function create(Request $request)
    {
        $user = $request->user();

        if (! $this->userHasCompleteBiodata()) {
            return redirect()->route('dashboard.translation')
                ->with('error', 'Lengkapi biodata terlebih dahulu sebelum mengajukan penerjemahan.');
        }

        if (! $this->userHasCompletedBasicListening()) {
            return redirect()->route('dashboard.translation')
                ->with('error', 'Anda belum mengikuti Basic Listening. Setelah nilai Attendance & Final Test terisi, Anda dapat mengajukan penerjemahan.');
        }

        return view('dashboard.translation.create', [
            'user' => $user,
        ]);
    }

    /* ============================= STORE =================================== */

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $this->userHasCompleteBiodata() || ! $this->userHasCompletedBasicListening()) {
            return redirect()->route('dashboard.translation')
                ->with('error', 'Anda belum memenuhi syarat untuk mengajukan penerjemahan.');
        }

        $validated = $request->validate([
            'bukti_pembayaran' => ['required', 'image', 'max:8192'],
            'source_text'      => ['required', 'string'],
        ], [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_pembayaran.image'    => 'Bukti pembayaran harus berupa gambar (PNG/JPG).',
            'bukti_pembayaran.max'      => 'Ukuran bukti pembayaran maksimal 8MB.',
            'source_text.required'      => 'Abstrak yang ingin diterjemahkan wajib diisi.',
        ]);

        // Hitung jumlah kata (copy dari Resource)
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $validated['source_text'])));
        $sourceWordCount = $plain === '' ? 0 : str_word_count(
            $plain,
            0,
            'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ'
        );

        // Simpan & kompres bukti pembayaran → webp
        $file = $request->file('bukti_pembayaran');
        $nama = Str::slug($user->name ?? 'pemohon', '_');
        $base = "proof_{$nama}.webp";

        $path = ImageTransformer::toWebpFromUploaded(
            uploaded: $file,
            targetDisk: 'public',
            targetDir: 'penerjemahan/images/payments',
            quality: 85,
            maxWidth: 1600,
            maxHeight: null,
            basename: $base
        )['path'];

        $record = Penerjemahan::create([
            'user_id'            => $user->id,
            'status'             => 'Menunggu',
            'bukti_pembayaran'   => $path,
            'source_text'        => $validated['source_text'],
            'source_word_count'  => $sourceWordCount,
            'submission_date'    => now(),
            // kolom lain (translated_text, translator_id, dll) biarkan default/null
        ]);

        return redirect()
            ->route('dashboard.translation')
            ->with('success', 'Permohonan penerjemahan berhasil dikirim. Silakan menunggu proses verifikasi dari Lembaga Bahasa.');
    }

    /* ============================= EDIT / UPDATE (opsional) ================= */

    public function edit(Penerjemahan $penerjemahan)
    {
        $user = Auth::user();

        if ($penerjemahan->user_id !== $user->id) {
            abort(403);
        }

        // logika selaras dengan EditPenerjemahan: hanya relevan saat ditolak
        return view('dashboard.translation.edit', [
            'user'         => $user,
            'penerjemahan' => $penerjemahan,
        ]);
    }

    public function update(Request $request, Penerjemahan $penerjemahan)
    {
        $user = $request->user();

        if ($penerjemahan->user_id !== $user->id) {
            abort(403);
        }

        // Di sini kamu bisa batasi: hanya boleh update saat status "Ditolak - ..."
        $rules = [
            'bukti_pembayaran' => ['nullable', 'image', 'max:8192'],
            'source_text'      => ['nullable', 'string'],
        ];

        $data = $request->validate($rules);

        if ($request->hasFile('bukti_pembayaran')) {
            $nama = Str::slug($user->name ?? 'pemohon', '_');
            $base = "proof_{$nama}.webp";

            $path = ImageTransformer::toWebpFromUploaded(
                uploaded: $request->file('bukti_pembayaran'),
                targetDisk: 'public',
                targetDir: 'penerjemahan/images/payments',
                quality: 85,
                maxWidth: 1600,
                maxHeight: null,
                basename: $base
            )['path'];

            $penerjemahan->bukti_pembayaran = $path;
        }

        if (! empty($data['source_text'])) {
            $penerjemahan->source_text = $data['source_text'];

            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $data['source_text'])));
            $penerjemahan->source_word_count = $plain === '' ? 0 : str_word_count(
                $plain,
                0,
                'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ'
            );
        }

        // Setelah diperbaiki, status bisa kamu kembalikan ke "Menunggu"
        $penerjemahan->status = 'Menunggu';

        $penerjemahan->save();

        return redirect()
            ->route('dashboard.translation')
            ->with('success', 'Permohonan penerjemahan berhasil diperbarui.');
    }
}
