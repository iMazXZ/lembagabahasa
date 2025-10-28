<?php

use Illuminate\Support\Facades\Route;

// Front & posts
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

// Cetak & laporan
use App\Http\Controllers\CetakGrupTesController;
use App\Http\Controllers\CetakNilaiGrupController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenerjemahanPdfController;

// Verifikasi & EPT PDF
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\EptSubmissionPdfController;

// Basic Listening (lama: MC / multi-soal)
use App\Http\Controllers\BasicListeningController;
use App\Http\Controllers\BasicListeningConnectController;
use App\Http\Controllers\BasicListeningQuizController;
use App\Http\Controllers\BasicListeningHistoryController;

// Basic Listening (baru: FIB 1 paragraf)
use App\Http\Controllers\BasicListeningQuizFibController;

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\BasicListeningProfileController;


/*
|--------------------------------------------------------------------------
| Front pages
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('front.home');

Route::get('/berita', [PostController::class, 'index'])
    ->defaults('type', 'news')
    ->name('front.news');

Route::get('/jadwal-ujian', [PostController::class, 'index'])
    ->defaults('type', 'schedule')
    ->name('front.schedule');

Route::get('/nilai-ujian', [PostController::class, 'index'])
    ->defaults('type', 'scores')
    ->name('front.scores');

Route::get('/post/{slug}', [PostController::class, 'show'])
    ->name('front.post.show');

Route::get('/login', fn () => redirect()->route('filament.admin.auth.login'))
    ->name('login');

/*
|--------------------------------------------------------------------------
| Cetak / Laporan
|--------------------------------------------------------------------------
*/

Route::get('/cetak-grup/{id}', [CetakGrupTesController::class, 'cetak'])
    ->name('grup.cetak');

Route::get('/grup/{id}/cetak-nilai', [CetakNilaiGrupController::class, 'cetak'])
    ->name('grup.cetak-nilai');

Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])
    ->middleware('auth')
    ->name('laporan.export.pdf');

Route::get('/laporan/export/all', [LaporanController::class, 'exportAllPdf'])
    ->middleware('auth')
    ->name('laporan.export.all.pdf');

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
| Basic Listening (index & sesi)
|--------------------------------------------------------------------------
*/

Route::get('/basic-listening', [BasicListeningController::class, 'index'])
    ->name('bl.index');

Route::get('/basic-listening/sessions/{session}', [BasicListeningController::class, 'show'])
    ->whereNumber('session')
    ->name('bl.session.show');

/*
|--------------------------------------------------------------------------
| Basic Listening – Connect Code (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/basic-listening/sessions/{session}/code', [BasicListeningConnectController::class, 'showForm'])
        ->whereNumber('session')
        ->name('bl.code.form');

    Route::post('/basic-listening/sessions/{session}/code', [BasicListeningConnectController::class, 'verify'])
        ->whereNumber('session')
        ->name('bl.code.verify');
});

/*
|--------------------------------------------------------------------------
| Basic Listening – Quiz Lama (MC / multi-soal) – Protected
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
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
| Basic Listening – Quiz Baru (FIB 1 paragraf + timer) – Protected
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/bl/quiz/{quiz}/start',  [BasicListeningQuizFibController::class, 'start'])
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

Route::middleware(['auth'])->group(function () {
    // pendaftar download miliknya sendiri (Admin/tutor boleh untuk testing)
    Route::get('/bl/certificate', [CertificateController::class, 'basicListening'])
        ->name('bl.certificate.download');
});

// Public (tanpa login): download/preview berdasar kode verifikasi
Route::get('/verification/{code}/basic-listening.pdf', [CertificateController::class, 'basicListeningByCode'])
    ->name('bl.certificate.bycode');

Route::middleware(['auth'])->group(function () {
    Route::post('/bl/group-number', [BasicListeningProfileController::class, 'updateGroupNumber'])
        ->name('bl.groupNumber.update');
}); 