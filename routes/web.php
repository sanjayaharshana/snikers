<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SnickersController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PreviewController;

Route::get('/', function () {
    return view('welcome');
});

// Snickers Campaign Routes
Route::get('/snickers', [SnickersController::class, 'index'])->name('snickers.campaign');
Route::post('/snickers/capture', [SnickersController::class, 'capture'])->name('snickers.capture');
Route::post('/snickers/process-first-selfie', [SnickersController::class, 'processFirstSelfie'])->name('snickers.process-first-selfie');
Route::post('/snickers/process-second-selfie', [SnickersController::class, 'processSecondSelfie'])->name('snickers.process-second-selfie');
Route::get('/snickers/image/{filename}', [SnickersController::class, 'getImage'])->name('snickers.image');

// Admin Routes
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/images/{id}', [AdminController::class, 'show'])->name('admin.show');
Route::get('/admin/images/{id}/edit', [AdminController::class, 'edit'])->name('admin.edit');
Route::put('/admin/images/{id}', [AdminController::class, 'update'])->name('admin.update');
Route::delete('/admin/images/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
Route::get('/admin/images/{id}/download/{type}', [AdminController::class, 'download'])->name('admin.download');

// Preview Routes
Route::get('/preview', [PreviewController::class, 'index'])->name('preview.kiosk');
