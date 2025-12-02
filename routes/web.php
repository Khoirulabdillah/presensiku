<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\IzinController;

Route::get('/', function () {
    return view('auth/login');
});

// Route tampilan pegawai — dibatasi hanya untuk pengguna terautentikasi
Route::get('/pegawai/home', function () {
    return view('pegawai.home');
})->middleware('auth')->name('pegawai.home');

// INDEX: tampilkan daftar pegawai
Route::get('/admin/pegawai', [PegawaiController::class, 'index'])
    ->name('admin.pegawai.index');
// ROUTE CRUD Pegawai
Route::get('/admin/pegawai/create', [PegawaiController::class, 'create'])
    ->name('admin.pegawai.create');

// (optional legacy admin registration form kept for compatibility)
Route::get('/auth/register', [App\Http\Controllers\PegawaiController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('/auth/register', [App\Http\Controllers\PegawaiController::class, 'storePegawaiRegistration'])->name('admin.register.store');

// STORE: simpan data pegawai (admin CRUD)
Route::post('/admin/pegawai', [PegawaiController::class, 'store'])
    ->name('admin.pegawai.store');

// EDIT: form edit pegawai (gunakan nip sebagai identifier)
Route::get('/admin/pegawai/{nip}/edit', [PegawaiController::class, 'edit'])
    ->name('admin.pegawai.edit');

// UPDATE: perbarui data pegawai
Route::put('/admin/pegawai/{nip}', [PegawaiController::class, 'update'])
    ->name('admin.pegawai.update');

// DELETE: hapus data pegawai
Route::delete('/admin/pegawai/{nip}', [PegawaiController::class, 'destroy'])
    ->name('admin.pegawai.destroy');

//Route untuk menampilkan dashboard setelah login
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Route izin untuk pegawai
    Route::get('/pegawai/izin', [IzinController::class, 'create'])->name('pegawai.izin.create');
    Route::post('/pegawai/izin', [IzinController::class, 'store'])->name('pegawai.izin.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
