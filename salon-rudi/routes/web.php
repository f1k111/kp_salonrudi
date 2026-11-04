<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', fn () => view('welcome'))->name('home');

// Menyimpan booking
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/kelola', fn () => view('booking-manage'))->name('booking.manage');
Route::post('/booking/kelola', [BookingController::class, 'lookup'])->name('booking.lookup');
Route::put('/booking/{booking}/jadwal', [BookingController::class, 'reschedule'])->name('booking.reschedule');

// Route bawaan Breeze untuk Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [BookingController::class, 'dashboard'])->name('dashboard');
    Route::put('/admin/bookings/{booking}', [BookingController::class, 'adminUpdate'])->name('admin.bookings.update');
    Route::post('/admin/bookings', [BookingController::class, 'adminStore'])->name('admin.bookings.store');
    Route::delete('/admin/bookings/{booking}', [BookingController::class, 'destroy'])->name('admin.bookings.destroy');
    Route::post('/kasir/bookings/{booking}/bayar', [BookingController::class, 'pay'])->name('kasir.bookings.pay');
});

// Route bawaan Breeze untuk profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route Login, Register, dan autentikasi Breeze lainnya
require __DIR__.'/auth.php';
