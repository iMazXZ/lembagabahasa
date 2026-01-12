<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Front & posts
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

// Cetak & laporan
use App\Http\Controllers\PenerjemahanPdfController;
use App\Http\Controllers\EptSubmissionPdfController;
use App\Http\Controllers\CertificateController;

// Verifikasi
use App\Http\Controllers\VerificationController;

// Basic Listening (Unified Controller)
use App\Http\Controllers\BasicListeningController;
use App\Http\Controllers\BasicListeningConnectController;
use App\Http\Controllers\BasicListeningQuizController;
use App\Http\Controllers\BasicListeningHistoryController;
use App\Http\Controllers\BasicListeningProfileController;
use App\Http\Controllers\BasicListeningScheduleController;
use App\Http\Controllers\BlSurveyController;
use App\Http\Controllers\ManualCertificateController;

// Middleware
use App\Http\Middleware\CountPostView;

// Dashboard per role
use App\Http\Controllers\RoleDashboardRedirectController;
use App\Http\Controllers\PendaftarDashboardController;
use App\Http\Controllers\TutorDashboardController;

// Dashboard fitur lain
use App\Http\Controllers\DashboardPasswordController;
use App\Http\Controllers\Dashboard\SubmitEptScoreController;
use App\Http\Controllers\Dashboard\TranslationController;
use App\Http\Controllers\TutorMahasiswaBulkExportController;

/*
|--------------------------------------------------------------------------
| Dashboard per Role (Blade, bukan Filament)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Gerbang utama setelah login / register
    Route::get('/dashboard', [RoleDashboardRedirectController::class, 'index'])
        ->name('dashboard');

    // Dashboard Pendaftar
    Route::get('/dashboard/pendaftar', [PendaftarDashboardController::class, 'index'])
        ->middleware('role:pendaftar')
        ->name('dashboard.pendaftar');

    // Dashboard Penerjemah
    Route::get('/dashboard/penerjemah', [\App\Http\Controllers\PenerjemahDashboardController::class, 'index'])
        ->middleware('role:Penerjemah')
        ->name('dashboard.penerjemah');
    
    Route::get('/dashboard/penerjemah/tugas', [\App\Http\Controllers\PenerjemahDashboardController::class, 'tugas'])
        ->middleware('role:Penerjemah')
        ->name('dashboard.penerjemah.tugas');

    Route::get('/dashboard/penerjemah/edit/{penerjemahan}', [\App\Http\Controllers\PenerjemahDashboardController::class, 'edit'])
        ->middleware('role:Penerjemah')
        ->name('dashboard.penerjemah.edit');

    Route::put('/dashboard/penerjemah/update/{penerjemahan}', [\App\Http\Controllers\PenerjemahDashboardController::class, 'update'])
        ->middleware('role:Penerjemah')
        ->name('dashboard.penerjemah.update');

    Route::post('/dashboard/penerjemah/selesai/{penerjemahan}', [\App\Http\Controllers\PenerjemahDashboardController::class, 'selesai'])
        ->middleware('role:Penerjemah')
        ->name('dashboard.penerjemah.selesai');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('front.home');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Front pages
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('front.home');

Route::get('/berita', [PostController::class, 'index'])
    ->defaults('type', 'news')
    ->name('front.news');

Route::get('/jadwal-ujian', [PostController::class, 'index'])
    ->defaults('type', 'schedule')
    ->name('front.schedule');

Route::get('/nilai-ujian', [PostController::class, 'index'])
    ->defaults('type', 'scores')
    ->name('front.scores');

Route::get('/post/{post:slug}', [PostController::class, 'show'])
    ->middleware(CountPostView::class)
    ->name('front.post.show');

Route::get('/login', fn () => redirect()->route('filament.admin.auth.login'))
    ->name('login');

/*
|--------------------------------------------------------------------------
| Penerjemahan PDF
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/penerjemahan/{record}/pdf', [PenerjemahanPdfController::class, 'show'])
        ->whereNumber('record')
        ->name('penerjemahan.pdf');
});

Route::get('/verification/{code}/penerjemahan.pdf', [PenerjemahanPdfController::class, 'byCode'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:30,1')
    ->name('verification.penerjemahan.pdf');

/*
|--------------------------------------------------------------------------
| EPT Submission PDF (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/ept-submissions/{submission}/pdf', [EptSubmissionPdfController::class, 'show'])
        ->name('ept-submissions.pdf');
});

/*
|--------------------------------------------------------------------------
| Verification (Public + Rate Limit)
|--------------------------------------------------------------------------
*/

Route::get('/verification/{code}/ept.pdf', [EptSubmissionPdfController::class, 'byCode'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:30,1')
    ->name('verification.ept.pdf');

Route::get('/verification', [VerificationController::class, 'index'])
    ->name('verification.index');

Route::get('/verification/{code}', [VerificationController::class, 'show'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:60,1')
    ->name('verification.show');

/*
|--------------------------------------------------------------------------
| Basic Listening – Index, Sesi, Jadwal
|--------------------------------------------------------------------------
*/

Route::get('/basic-listening', [BasicListeningController::class, 'index'])
    ->name('bl.index');

Route::get('/basic-listening/sessions/{session}', [BasicListeningController::class, 'show'])
    ->whereNumber('session')
    ->name('bl.session.show');

Route::get('/basic-listening/schedule', [BasicListeningScheduleController::class, 'index'])
    ->name('bl.schedule');

/*
|--------------------------------------------------------------------------
| Basic Listening – Connect Code (Protected + biodata lengkap)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'bl.profile'])->group(function () {
    Route::get('/basic-listening/sessions/{session}/code', [BasicListeningConnectController::class, 'showForm'])
        ->whereNumber('session')
        ->name('bl.code.form');

    Route::post('/basic-listening/sessions/{session}/code', [BasicListeningConnectController::class, 'verify'])
        ->whereNumber('session')
        ->name('bl.code.verify');
});

/*
|--------------------------------------------------------------------------
| Basic Listening – Quiz (All Types: MC, TF, FIB)
| Protected + biodata lengkap
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'bl.profile'])->group(function () {
    // Tampilkan soal (handle dynamic view FIB/MC)
    Route::get('/basic-listening/quiz/{attempt}', [BasicListeningQuizController::class, 'show'])
        ->whereNumber('attempt')
        ->name('bl.quiz.show');

    // Simpan jawaban (handle single answer & array answers)
    Route::post('/basic-listening/quiz/{attempt}/answer', [BasicListeningQuizController::class, 'answer'])
        ->whereNumber('attempt')
        ->name('bl.quiz.answer');

    // Submit akhir
    Route::post('/basic-listening/quiz/{attempt}/submit', [BasicListeningQuizController::class, 'submit'])
        ->whereNumber('attempt')
        ->name('bl.quiz.submit');

    // Force submit (timeout)
    Route::post('/bl-quiz/{attempt}/force-submit', [BasicListeningQuizController::class, 'forceSubmit'])
        ->name('bl.quiz.force-submit');
        
    // Continue attempt (redirector)
    Route::get('/basic-listening/quiz/{attempt}/continue', [BasicListeningController::class, 'continue'])
        ->whereNumber('attempt')
        ->name('bl.quiz.continue');
});

/*
|--------------------------------------------------------------------------
| Basic Listening – History (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/basic-listening/history', [BasicListeningHistoryController::class, 'index'])
        ->name('bl.history');

    Route::get('/basic-listening/history/{attempt}', [BasicListeningHistoryController::class, 'show'])
        ->whereNumber('attempt')
        ->name('bl.history.show');

    Route::get('/bl/tutor-mahasiswa/export', TutorMahasiswaBulkExportController::class)
        ->middleware('signed')
        ->name('bl.tutor-mahasiswa.export');
});

/*
|--------------------------------------------------------------------------
| Basic Listening – Sertifikat, Verifikasi Sertifikat, Group Number
|--------------------------------------------------------------------------
*/

// Download sertifikat (login)
Route::middleware('auth')->group(function () {
    Route::get('/bl/certificate', [CertificateController::class, 'basicListening'])
        ->name('bl.certificate.download');

    Route::post('/bl/group-number', [BasicListeningProfileController::class, 'updateGroupNumber'])
        ->name('bl.groupNumber.update');
});

// Public (tanpa login): download/preview berdasar kode verifikasi
Route::get('/verification/{code}/basic-listening.pdf', [CertificateController::class, 'basicListeningByCode'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:30,1')
    ->name('bl.certificate.bycode');

/*
|--------------------------------------------------------------------------
| Manual Certificate Download (Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/manual-certificate/{certificate}/download', [ManualCertificateController::class, 'download'])
        ->name('manual-certificate.download');
});

// Public download by verification code (tanpa login)
Route::get('/verification/{code}/manual-certificate.pdf', [ManualCertificateController::class, 'downloadByCode'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:30,1')
    ->name('manual-certificate.public-download');

// Public download by certificate ID (untuk individual semester)
Route::get('/manual-certificate/{id}/pdf', [ManualCertificateController::class, 'downloadById'])
    ->whereNumber('id')
    ->middleware('throttle:30,1')
    ->name('manual-certificate.download-by-id');

/*
|--------------------------------------------------------------------------
| EPT CBT System (Protected)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Ept\EptController;
use App\Http\Controllers\Ept\EptLauncherController;
use App\Http\Controllers\Ept\EptQuizController;
use App\Http\Controllers\Ept\EptHistoryController;

Route::middleware(['auth', 'biodata.complete'])->prefix('ept')->group(function () {
    Route::get('/', [EptController::class, 'index'])->name('ept.index');
    Route::get('/schedule', [EptController::class, 'schedule'])->name('ept.schedule');
    Route::get('/token', [EptController::class, 'token'])->name('ept.token');
    Route::get('/diagnostic', [EptController::class, 'diagnostic'])->name('ept.diagnostic');
    
    // CBT Launcher
    Route::get('/launcher', [EptLauncherController::class, 'index'])->name('ept.launcher');
    Route::post('/launcher/start', [EptLauncherController::class, 'start'])->name('ept.launcher.start');
    Route::post('/launcher/validate-token', [EptLauncherController::class, 'validateToken'])->name('ept.launcher.validate');
    
    // Quiz
    Route::get('/quiz/{attempt}', [EptQuizController::class, 'show'])->name('ept.quiz.show');
    Route::post('/quiz/{attempt}/answer', [EptQuizController::class, 'answer'])->name('ept.quiz.answer');
    Route::post('/quiz/{attempt}/next-section', [EptQuizController::class, 'nextSection'])->name('ept.quiz.nextSection');
    Route::post('/quiz/{attempt}/submit', [EptQuizController::class, 'submit'])->name('ept.quiz.submit');
    Route::post('/quiz/{attempt}/ping', [EptQuizController::class, 'ping'])->name('ept.quiz.ping');
    
    // History & Certificate
    Route::get('/history', [EptHistoryController::class, 'index'])->name('ept.history.index');
    Route::get('/history/{attempt}', [EptHistoryController::class, 'show'])->name('ept.history.show');
    Route::get('/history/{attempt}/certificate', [EptHistoryController::class, 'certificate'])->name('ept.history.certificate');
    Route::get('/history/{attempt}/certificate/preview', [EptHistoryController::class, 'certificatePreview'])->name('ept.history.certificate.preview');
});

/*
|--------------------------------------------------------------------------
| Basic Listening – Survey (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // start wizard: pilih tutor/supervisor dulu
    Route::get('/bl/survey/start', [BlSurveyController::class, 'start'])
        ->name('bl.survey.start');
    Route::post('/bl/survey/start', [BlSurveyController::class, 'startSubmit'])
        ->name('bl.survey.start.submit');

    // existing
    Route::get('/bl/survey/required', [BlSurveyController::class, 'redirectToRequired'])
        ->name('bl.survey.required');

    Route::get('/bl/survey/{survey}', [BlSurveyController::class, 'show'])
        ->whereNumber('survey')
        ->name('bl.survey.show');

    Route::post('/bl/survey/{survey}', [BlSurveyController::class, 'submit'])
        ->whereNumber('survey')
        ->name('bl.survey.submit');

    Route::get('/bl/survey/success', [BlSurveyController::class, 'success'])
        ->name('bl.survey.success');

    Route::get('/bl/survey/edit-choice', [BlSurveyController::class, 'editChoice'])
        ->name('bl.survey.edit-choice');

    Route::post('/bl/survey/edit-choice', [BlSurveyController::class, 'updateChoice'])
        ->name('bl.survey.update-choice');

    Route::get('/bl/survey/reset-choice', [BlSurveyController::class, 'resetChoice'])
        ->name('bl.survey.reset-choice');

    // Export hasil survey (PDF)
    Route::get('/bl/survey-results/export', [\App\Http\Controllers\BlSurveyResultsExportController::class, '__invoke'])
        ->name('bl.survey-results.export');
});

/*
|--------------------------------------------------------------------------
| Basic Listening – Profil, Biodata Dashboard, Password (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/bl/complete-profile', [BasicListeningProfileController::class, 'showCompleteForm'])
        ->name('bl.profile.complete');

    Route::post('/bl/complete-profile', [BasicListeningProfileController::class, 'submitCompleteForm'])
        ->name('bl.profile.complete.submit');

    Route::get('/dashboard/biodata', [BasicListeningProfileController::class, 'showDashboardBiodata'])
        ->name('dashboard.biodata');
    Route::delete('/dashboard/biodata/photo', [BasicListeningProfileController::class, 'deletePhoto'])
        ->name('dashboard.biodata.photo.delete');

    Route::post('/dashboard/password', [DashboardPasswordController::class, 'update'])
        ->name('dashboard.password.update');

    // EPT Registration (S2 only)
    Route::get('/dashboard/ept-registration', [\App\Http\Controllers\Dashboard\EptRegistrationController::class, 'index'])
        ->name('dashboard.ept-registration.index');
    Route::post('/dashboard/ept-registration', [\App\Http\Controllers\Dashboard\EptRegistrationController::class, 'store'])
        ->name('dashboard.ept-registration.store');
    Route::get('/dashboard/ept-registration/kartu', [\App\Http\Controllers\Dashboard\EptRegistrationController::class, 'kartuPeserta'])
        ->name('dashboard.ept-registration.kartu');

    // API untuk update nomor WhatsApp via AJAX (tanpa OTP - legacy)
    Route::post('/api/whatsapp/update', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'whatsapp' => ['required', 'string', 'max:20'],
        ]);

        $normalized = \App\Support\NormalizeWhatsAppNumber::normalize($request->whatsapp);

        if (!$normalized) {
            return response()->json([
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid',
            ], 422);
        }

        $request->user()->update(['whatsapp' => $normalized]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil disimpan',
        ]);
    })->name('api.whatsapp.update');

    // API Kirim OTP WhatsApp (Cek setting dari database)
    Route::post('/api/whatsapp/send-otp', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'whatsapp' => ['required', 'string', 'max:20'],
        ]);

        $normalized = \App\Support\NormalizeWhatsAppNumber::normalize($request->whatsapp);

        if (!$normalized) {
            return response()->json([
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid',
            ], 422);
        }

        // Cek apakah nomor sudah dipakai user lain
        $existingUser = \App\Models\User::where('whatsapp', $normalized)
            ->where('id', '!=', $request->user()->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp ini sudah terdaftar di akun lain.',
            ], 422);
        }

        $user = $request->user();

        // Cek apakah OTP diaktifkan dari pengaturan situs
        if (\App\Models\SiteSetting::isOtpEnabled()) {
            // OTP AKTIF: Generate dan kirim OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(5);

            $user->update([
                'whatsapp' => $normalized,
                'whatsapp_otp' => $otp,
                'whatsapp_otp_expires_at' => $expiresAt,
                'whatsapp_verified_at' => null,
            ]);

            // Kirim OTP via WhatsApp
            $waService = app(\App\Services\WhatsAppService::class);

            if (!$waService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan WhatsApp tidak tersedia',
                ], 503);
            }

            $sent = $waService->sendOtp($normalized, $otp);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim ke WhatsApp Anda',
                    'skip_otp' => false,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP. Pastikan nomor WhatsApp aktif.',
            ], 500);
        }

        // OTP NONAKTIF: Langsung simpan dan verifikasi
        $user->update([
            'whatsapp' => $normalized,
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
            'whatsapp_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil disimpan!',
            'skip_otp' => true,
        ]);
    })->name('api.whatsapp.send-otp');

    // API Verifikasi OTP WhatsApp
    Route::post('/api/whatsapp/verify-otp', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $inputOtp = $request->input('otp');

        // Cek apakah ada OTP yang pending
        if (!$user->whatsapp_otp || !$user->whatsapp_otp_expires_at) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada OTP yang pending. Silakan kirim ulang OTP.',
            ], 400);
        }

        // Cek expired
        if (now()->isAfter($user->whatsapp_otp_expires_at)) {
            $user->update([
                'whatsapp_otp' => null,
                'whatsapp_otp_expires_at' => null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang.',
            ], 400);
        }

        // Verifikasi OTP
        if ($user->whatsapp_otp !== $inputOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid.',
            ], 400);
        }

        // Sukses - tandai sebagai verified
        $user->update([
            'whatsapp_verified_at' => now(),
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil diverifikasi!',
        ]);
    })->name('api.whatsapp.verify-otp');

    // API Simpan WhatsApp tanpa OTP (untuk saat OTP disabled)
    Route::post('/api/whatsapp/save-only', function (Request $request) {
        $request->validate([
            'whatsapp' => ['required', 'string', 'min:10', 'max:20'],
        ]);

        $user = $request->user();
        $whatsapp = preg_replace('/[^0-9]/', '', $request->input('whatsapp'));

        // Cek apakah nomor sudah digunakan user lain
        $exists = \App\Models\User::where('whatsapp', $whatsapp)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp ini sudah terdaftar di akun lain.',
            ], 422);
        }

        // Simpan nomor tanpa verifikasi
        $user->update([
            'whatsapp' => $whatsapp,
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
            // Tidak set whatsapp_verified_at karena tidak diverifikasi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil disimpan!',
        ]);
    })->name('api.whatsapp.save-only');

    // Dismiss Welcome Modal
    Route::post('/dismiss-welcome', function (Request $request) {
        $user = $request->user();
        $user->has_seen_welcome = true;
        $user->save();

        return response()->json(['success' => true]);
    })->name('api.dismiss-welcome');

    // Delete WhatsApp Number
    Route::post('/delete-whatsapp', function (Request $request) {
        $user = $request->user();
        
        $user->update([
            'whatsapp' => null,
            'whatsapp_verified_at' => null,
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil dihapus.',
        ]);
    })->name('api.whatsapp.delete');

    // Reset Biodata (SRN, Prodi, Year, Nilai BL)
    Route::post('/reset-biodata', function (Request $request) {
        $user = $request->user();
        
        $user->update([
            'srn' => null,
            'prody_id' => null,
            'year' => null,
            'nilaibasiclistening' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Biodata berhasil direset.',
        ]);
    })->name('api.biodata.reset');
});

/*
|--------------------------------------------------------------------------
| Dashboard Pendaftar – Pengajuan Surat Rekomendasi (Protected, role:pendaftar)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pendaftar'])->group(function () {
    Route::get('/dashboard/surat-rekomendasi', [SubmitEptScoreController::class, 'index'])
        ->name('dashboard.ept');

    Route::post('/dashboard/surat-rekomendasi', [SubmitEptScoreController::class, 'store'])
        ->name('dashboard.ept.store');
});

/*
|--------------------------------------------------------------------------
| Dashboard Pendaftar – Penerjemahan Dokumen Abstrak (Protected, role:pendaftar)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:pendaftar'])
    ->prefix('dashboard')
    ->group(function () {
        // daftar & riwayat
        Route::get('/translation', [TranslationController::class, 'index'])
            ->name('dashboard.translation');

        // form permintaan baru
        Route::get('/translation/create', [TranslationController::class, 'create'])
            ->name('dashboard.translation.create');

        Route::post('/translation', [TranslationController::class, 'store'])
            ->name('dashboard.translation.store');

        // perbaikan permohonan
        Route::get('/translation/{penerjemahan}/edit', [TranslationController::class, 'edit'])
            ->name('dashboard.translation.edit');

        Route::put('/translation/{penerjemahan}', [TranslationController::class, 'update'])
            ->name('dashboard.translation.update');
    });

Route::post('/bl/quiz/ping/{attempt}', [BasicListeningQuizController::class, 'ping'])
    ->middleware('auth')
    ->name('bl.quiz.ping');

/*
|--------------------------------------------------------------------------
| Admin: EPT Group - Kirim WA Individual
|--------------------------------------------------------------------------
*/
Route::post('/admin/ept-group/{group}/send-wa/{registration}', function (
    \App\Models\EptGroup $group,
    \App\Models\EptRegistration $registration
) {
    $user = $registration->user;
    
    if (!$user->whatsapp || !$user->whatsapp_verified_at) {
        return response()->json(['success' => false, 'message' => 'User tidak memiliki WhatsApp terverifikasi.'], 400);
    }
    
    if (!$group->jadwal) {
        return response()->json(['success' => false, 'message' => 'Grup belum memiliki jadwal.'], 400);
    }
    
    // Determine which tes number
    $tesNum = match(true) {
        $registration->grup_1_id === $group->id => 1,
        $registration->grup_2_id === $group->id => 2,
        $registration->grup_3_id === $group->id => 3,
        default => null,
    };
    
    try {
        $jadwal = $group->jadwal->translatedFormat('l, d F Y H:i');
        $dashboardUrl = route('dashboard.ept-registration.index');

        $message = "*Jadwal Tes EPT Ditetapkan*\n\n";
        $message .= "Yth. *{$user->name}*,\n\n";
        $message .= "Jadwal *Tes ke-{$tesNum}* EPT Anda telah ditetapkan:\n\n";
        $message .= "*Grup:* {$group->name}\n";
        $message .= "*Waktu:* {$jadwal} WIB\n";
        $message .= "*Lokasi:* {$group->lokasi}\n\n";
        $message .= "Silakan download Kartu Peserta melalui:\n{$dashboardUrl}\n\n";
        $message .= "_Wajib membawa kartu peserta dan KTP/Kartu Mahasiswa._";

        $sent = app(\App\Services\WhatsAppService::class)->sendMessage($user->whatsapp, $message);

        if ($sent) {
            return response()->json(['success' => true, 'message' => "Pesan berhasil dikirim ke {$user->name}"]);
        }
        
        return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan. Cek koneksi WhatsApp.'], 500);
            
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
})->middleware(['auth', 'can:view_any_ept::group'])->name('admin.ept-group.send-wa-single');

/*
|--------------------------------------------------------------------------
| Admin: Crop Bukti Pembayaran Penerjemahan
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/crop-bukti/{penerjemahan}', [\App\Http\Controllers\Admin\CropBuktiController::class, 'show'])
        ->name('admin.crop-bukti.show');
    
    Route::post('/crop-bukti/{penerjemahan}', [\App\Http\Controllers\Admin\CropBuktiController::class, 'save'])
        ->name('admin.crop-bukti.save');
    
    Route::post('/crop-bukti/{penerjemahan}/restore', [\App\Http\Controllers\Admin\CropBuktiController::class, 'restore'])
        ->name('admin.crop-bukti.restore');
    
    // Export Bukti Preview & Generate
    Route::get('/export-bukti-preview', [\App\Http\Controllers\Admin\ExportBuktiController::class, 'preview'])
        ->name('admin.export-bukti.preview');
    
    Route::post('/export-bukti-generate', [\App\Http\Controllers\Admin\ExportBuktiController::class, 'generate'])
        ->name('admin.export-bukti.generate');
    
    Route::post('/export-bukti-crop-save', [\App\Http\Controllers\Admin\ExportBuktiController::class, 'cropSave'])
        ->name('admin.export-bukti.crop-save');
});
