<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CetakNilaiGrupController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\CetakGrupTesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

Route::get('/', [HomeController::class, 'index'])->name('front.home');

// index per kategori
Route::get('/berita', fn() => app(PostController::class)->index('news'))->name('front.news');
Route::get('/jadwal-ujian', fn() => app(PostController::class)->index('schedule'))->name('front.schedule');
Route::get('/nilai-ujian', fn() => app(PostController::class)->index('scores'))->name('front.scores');

// detail post
Route::get('/post/{slug}', [PostController::class, 'show'])->name('front.post.show');

// route yang sudah ada
Route::get('/cetak-grup/{id}', [CetakGrupTesController::class, 'cetak'])->name('grup.cetak');
Route::get('/grup/{id}/cetak-nilai', [CetakNilaiGrupController::class, 'cetak'])->name('grup.cetak-nilai');
Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->middleware('auth')->name('laporan.export.pdf');
Route::get('/laporan/export/all', [LaporanController::class, 'exportAllPdf'])->middleware('auth')->name('laporan.export.all.pdf');
