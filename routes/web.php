<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CetakNilaiGrupController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cetak-grup/{id}', [\App\Http\Controllers\CetakGrupTesController::class, 'cetak'])->name('grup.cetak');
Route::get('/grup/{id}/cetak-nilai', [CetakNilaiGrupController::class, 'cetak'])->name('grup.cetak-nilai');