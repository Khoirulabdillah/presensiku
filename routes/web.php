<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\OfficeSettingController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('auth/login');
});

// Route tampilan pegawai — dibatasi hanya untuk pengguna terautentikasi
Route::get('/pegawai/home', function () {
    return view('pegawai.home');
})->middleware('auth')->name('pegawai.home');

// INDEX: tampilkan daftar pegawai
Route::get('/admin/pegawai', [AdminController::class, 'pegawai'])
    ->middleware(['auth', 'role:admin'])->name('admin.pegawai.index');
// ROUTE CRUD Pegawai
Route::get('/admin/pegawai/create', [PegawaiController::class, 'create'])
    ->middleware(['auth', 'role:admin'])->name('admin.pegawai.create');

// (optional legacy admin registration form kept for compatibility)
Route::get('/auth/register', [App\Http\Controllers\PegawaiController::class, 'showRegistrationForm'])->name('admin.register');
Route::post('/auth/register', [App\Http\Controllers\PegawaiController::class, 'storePegawaiRegistration'])->name('admin.register.store');

// STORE: simpan data pegawai (admin CRUD)
Route::post('/admin/pegawai', [PegawaiController::class, 'store'])
    ->middleware(['auth', 'role:admin'])->name('admin.pegawai.store');

// EDIT: form edit pegawai (gunakan nip sebagai identifier)
Route::get('/admin/pegawai/{nip}/edit', [PegawaiController::class, 'edit'])
    ->middleware(['auth', 'role:admin'])->name('admin.pegawai.edit');

// UPDATE: perbarui data pegawai
Route::put('/admin/pegawai/{nip}', [PegawaiController::class, 'update'])
    ->middleware(['auth', 'role:admin'])->name('admin.pegawai.update');

// DELETE: hapus data pegawai
Route::delete('/admin/pegawai/{nip}', [PegawaiController::class, 'destroy'])
    ->middleware(['auth', 'role:admin'])->name('admin.pegawai.destroy');

// Office Settings
Route::get('/admin/office-settings', [AdminController::class, 'officeSettings'])
    ->middleware(['auth', 'role:admin'])->name('admin.office-settings.index');
Route::put('/admin/office-settings', [AdminController::class, 'updateOfficeSettings'])
    ->middleware(['auth', 'role:admin'])->name('admin.office-settings.update');

// Admin Presensi
Route::get('/admin/presensi', [AdminController::class, 'presensi'])
    ->middleware(['auth', 'role:admin'])->name('admin.presensi.index');

// Admin Izin
Route::get('/admin/izin', [AdminController::class, 'izin'])
    ->middleware(['auth', 'role:admin'])->name('admin.izin.index');
Route::get('/admin/izin/{id}', [IzinController::class, 'show'])
    ->middleware(['auth', 'role:admin'])->name('admin.izin.show');
Route::get('/admin/izin/{id}/edit', [IzinController::class, 'edit'])
    ->middleware(['auth', 'role:admin'])->name('admin.izin.edit');
Route::put('/admin/izin/{id}', [IzinController::class, 'update'])
    ->middleware(['auth', 'role:admin'])->name('admin.izin.update');
Route::delete('/admin/izin/{id}', [IzinController::class, 'destroy'])
    ->middleware(['auth', 'role:admin'])->name('admin.izin.destroy');

// Admin Setting Waktu
Route::get('/admin/setting-waktu', [AdminController::class, 'settingWaktu'])
    ->middleware(['auth', 'role:admin'])->name('admin.setting-waktu.index');

//Route untuk menampilkan dashboard setelah login
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

//Route izin untuk pegawai
    Route::get('/pegawai/izin', [IzinController::class, 'index'])->middleware('auth')->name('pegawai.izin.index');
    Route::get('/pegawai/izin/create', [IzinController::class, 'create'])->middleware('auth')->name('pegawai.izin.create');
    Route::post('/pegawai/izin', [IzinController::class, 'store'])->middleware('auth')->name('pegawai.izin.store');
    Route::get('/pegawai/izin/{id}', [IzinController::class, 'showPegawai'])->middleware('auth')->name('pegawai.izin.show');
    Route::get('/pegawai/izin/{id}/edit', [IzinController::class, 'editPegawai'])->middleware('auth')->name('pegawai.izin.edit');
    Route::put('/pegawai/izin/{id}', [IzinController::class, 'updatePegawai'])->middleware('auth')->name('pegawai.izin.update');
    Route::delete('/pegawai/izin/{id}', [IzinController::class, 'destroyPegawai'])->middleware('auth')->name('pegawai.izin.destroy');

//Route presensi untuk pegawai
    Route::get('/pegawai/presensi', [PresensiController::class, 'index'])->middleware('auth')->name('pegawai.presensi.index');
    Route::post('/pegawai/presensi', [PresensiController::class, 'store'])->middleware('auth')->name('pegawai.presensi.store');

// Serve storage images when public/storage symlink is not available
Route::get('/storage/image/{path}', [StorageController::class, 'image'])->where('path', '.*')->name('storage.image');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
