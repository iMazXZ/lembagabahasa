<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CetakGrupTesController;
use App\Http\Controllers\CetakNilaiGrupController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\EptSubmissionPdfController;

/*
|--------------------------------------------------------------------------
| Front pages
|--------------------------------------------------------------------------
*/

// Beranda: ambil 3 kategori (news/schedule/scores) untuk section di welcome
Route::get('/', [HomeController::class, 'index'])->name('front.home');

// Daftar per kategori
Route::get('/berita', [PostController::class, 'index'])
    ->defaults('type', 'news')
    ->name('front.news');

Route::get('/jadwal-ujian', [PostController::class, 'index'])
    ->defaults('type', 'schedule')
    ->name('front.schedule');

Route::get('/nilai-ujian', [PostController::class, 'index'])
    ->defaults('type', 'scores')
    ->name('front.scores');

// Detail post
Route::get('/post/{slug}', [PostController::class, 'show'])
    ->name('front.post.show');


/*
|--------------------------------------------------------------------------
| Existing routes (tetap)
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
| Protected: Export PDF Hasil Terjemahan
| - Hanya bisa diakses jika sudah login.
| - Logika akses detail ada di controller (admin/staf kapan saja; pendaftar
|   hanya jika status = Selesai dan milik sendiri).
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Canonical (disarankan dipakai ke depannya)
    Route::get('/penerjemahan/{penerjemahan}/pdf', [PdfExportController::class, 'penerjemahan'])
        ->name('penerjemahan.pdf');

    // Alias/kompatibilitas dengan URL lama milikmu
    Route::get('/export/penerjemahan/{penerjemahan}', [PdfExportController::class, 'penerjemahan'])
        ->name('export.penerjemahan.pdf');

    // Regenerate (khusus Admin/Staf/Kepala) – pakai POST
    Route::post('/penerjemahan/{penerjemahan}/pdf/regenerate', [PdfExportController::class, 'regenerate'])
        ->name('penerjemahan.pdf.regenerate');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/ept-submissions/{submission}/pdf', [EptSubmissionPdfController::class, 'show'])
        ->name('ept-submissions.pdf');
});

Route::get('/verification/{code}/ept.pdf', [EptSubmissionPdfController::class, 'byCode'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:30,1') // rate-limit opsional
    ->name('verification.ept.pdf');

Route::get('/verification', [VerificationController::class, 'index'])
    ->name('verification.index');

Route::get('/verification/{code}', [VerificationController::class, 'show'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->middleware('throttle:60,1')   // atau lebih ketat: throttle:20,1
    ->name('verification.show');

