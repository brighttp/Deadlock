<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Anggota\LoanController as AnggotaLoanController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Registrasi mandiri (revisi asesor): akun yang dibuat selalu ber-role anggota.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');

    // Lebih ketat dari login supaya tidak bisa dipakai membuat akun massal.
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profil.password');

    // Katalog: Admin hanya melihat, Anggota bisa meminjam.
    Route::get('/katalog', CatalogController::class)->name('katalog');

    Route::middleware('role:anggota')->group(function () {
        Route::post('/katalog/{unit}/pinjam', [AnggotaLoanController::class, 'store'])->name('pinjam');
        Route::get('/pinjaman-saya', [AnggotaLoanController::class, 'index'])->name('pinjaman-saya');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('kategori', CategoryController::class)
            ->parameters(['kategori' => 'category'])
            ->except('show');
        Route::resource('unit', UnitController::class)->except('show');
        Route::resource('anggota', MemberController::class)
            ->parameters(['anggota' => 'member'])
            ->except('show');

        Route::get('/pinjaman', [AdminLoanController::class, 'index'])->name('pinjaman.index');
        Route::post('/pinjaman/{loan}/kembali', [AdminLoanController::class, 'kembali'])->name('pinjaman.kembali');

        Route::get('/riwayat', [HistoryController::class, 'index'])->name('riwayat.index');
        Route::get('/riwayat/cetak', [HistoryController::class, 'cetak'])->name('riwayat.cetak');
        Route::get('/riwayat/pdf', [HistoryController::class, 'pdf'])->name('riwayat.pdf');

        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
        Route::post('/monitoring/pantau', [MonitoringController::class, 'pantau'])->name('monitoring.pantau');
    });
});
