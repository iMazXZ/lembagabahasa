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

// Basic Listening (lama: MC / multi-soal)
use App\Http\Controllers\BasicListeningController;
use App\Http\Controllers\BasicListeningConnectController;
use App\Http\Controllers\BasicListeningQuizController;
use App\Http\Controllers\BasicListeningHistoryController;

// Basic Listening (baru: FIB 1 paragraf)
use App\Http\Controllers\BasicListeningQuizFibController;
use App\Http\Controllers\BasicListeningProfileController;
use App\Http\Controllers\BasicListeningScheduleController;
use App\Http\Controllers\BlSurveyController;

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
| Basic Listening – Quiz Lama (MC / multi-soal) – Protected + biodata lengkap
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'bl.profile'])->group(function () {
    Route::get('/basic-listening/quiz/{attempt}', [BasicListeningQuizController::class, 'show'])
        ->whereNumber('attempt')
        ->name('bl.quiz.show');

    Route::post('/basic-listening/quiz/{attempt}/answer', [BasicListeningQuizController::class, 'answer'])
        ->whereNumber('attempt')
        ->name('bl.quiz.answer');

    Route::post('/basic-listening/quiz/{attempt}/submit', [BasicListeningQuizController::class, 'submit'])
        ->whereNumber('attempt')
        ->name('bl.quiz.submit');

    Route::get('/basic-listening/quiz/{attempt}/continue', [BasicListeningController::class, 'continue'])
        ->whereNumber('attempt')
        ->name('bl.quiz.continue');

    Route::post('/bl-quiz/{attempt}/force-submit', [BasicListeningQuizController::class, 'forceSubmit'])
        ->name('bl.quiz.force-submit');
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
});

/*
|--------------------------------------------------------------------------
| Basic Listening – Quiz Baru (FIB 1 paragraf + timer)
| Protected + biodata lengkap
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'bl.profile'])->group(function () {
    Route::post('/bl/quiz/{quiz}/start', [BasicListeningQuizFibController::class, 'start'])
        ->whereNumber('quiz')
        ->name('bl.start');

    Route::get('/bl/quiz/{quiz}', [BasicListeningQuizFibController::class, 'show'])
        ->whereNumber('quiz')
        ->name('bl.quiz');

    Route::post('/bl/quiz/{attempt}/fib-answer', [BasicListeningQuizFibController::class, 'answer'])
        ->whereNumber('attempt')
        ->name('bl.quiz.fib.answer');

    Route::post('/bl/quiz/{quiz}/submit', [BasicListeningQuizFibController::class, 'submit'])
        ->whereNumber('quiz')
        ->name('bl.submit');
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

    Route::post('/dashboard/password', [DashboardPasswordController::class, 'update'])
        ->name('dashboard.password.update');
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