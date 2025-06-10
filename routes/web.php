<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CetakNilaiGrupController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cetak-grup/{id}', [\App\Http\Controllers\CetakGrupTesController::class, 'cetak'])->name('grup.cetak');
Route::get('/grup/{id}/cetak-nilai', [CetakNilaiGrupController::class, 'cetak'])->name('grup.cetak-nilai');
Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])
    ->middleware('auth')
    ->name('laporan.export.pdf');
Route::get('/laporan/export/all', [LaporanController::class, 'exportAllPdf'])
    ->middleware('auth')
    ->name('laporan.export.all.pdf');