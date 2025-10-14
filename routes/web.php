<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SnickersController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return redirect()->route('snickers.campaign');
});

// Snickers Campaign Routes
Route::get('/snickers', [SnickersController::class, 'index'])->name('snickers.campaign');
Route::post('/snickers/capture', [SnickersController::class, 'capture'])->name('snickers.capture');
Route::post('/snickers/process-first-selfie', [SnickersController::class, 'processFirstSelfie'])->name('snickers.process-first-selfie');
Route::post('/snickers/process-second-selfie', [SnickersController::class, 'processSecondSelfie'])->name('snickers.process-second-selfie');
Route::get('/snickers/image/{filename}', [SnickersController::class, 'getImage'])->name('snickers.image');
Route::get('/snickers/test-step4', [SnickersController::class, 'testStep4Data'])->name('snickers.test-step4');
Route::post('/snickers/check-job-status', [SnickersController::class, 'checkJobStatus'])->name('snickers.check-job-status');
Route::get('/snickers/processing-jobs', [SnickersController::class, 'getProcessingJobs'])->name('snickers.processing-jobs');

// Admin Routes
Route::get('/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/queue-jobs', [AdminController::class, 'queueJobs'])->name('admin.queue-jobs');
Route::post('/admin/queue-jobs/{id}/retry', [AdminController::class, 'retryJob'])->name('admin.retry-job');
Route::delete('/admin/queue-jobs/{id}/delete', [AdminController::class, 'deleteJob'])->name('admin.delete-job');
Route::post('/admin/queue-jobs/clear', [AdminController::class, 'clearQueue'])->name('admin.clear-queue');
Route::get('/admin/images/{id}', [AdminController::class, 'show'])->name('admin.show');
Route::get('/admin/images/{id}/framed-image', [AdminController::class, 'framedImage'])->name('admin.framedImage');
Route::get('/admin/images/{id}/edit', [AdminController::class, 'edit'])->name('admin.edit');
Route::put('/admin/images/{id}', [AdminController::class, 'update'])->name('admin.update');
Route::delete('/admin/images/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');
Route::get('/admin/images/{id}/download/{type}', [AdminController::class, 'download'])->name('admin.download');
Route::post('/admin/images/{id}/generate-happy', [AdminController::class, 'generateHappy'])->name('admin.generate-happy');

// Admin Settings
Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings');
Route::post('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');

// Image Management Routes
Route::middleware(['admin'])->prefix('admin/images')->name('admin.images.')->group(function () {
    Route::get('/', [App\Http\Controllers\ImageController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\ImageController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\ImageController::class, 'store'])->name('store');
    Route::get('/{image}', [App\Http\Controllers\ImageController::class, 'show'])->name('show');
    Route::get('/{image}/edit', [App\Http\Controllers\ImageController::class, 'edit'])->name('edit');
    Route::put('/{image}', [App\Http\Controllers\ImageController::class, 'update'])->name('update');
    Route::delete('/{image}', [App\Http\Controllers\ImageController::class, 'destroy'])->name('destroy');
    Route::get('/{image}/download/{type}', [App\Http\Controllers\ImageController::class, 'download'])->name('download');
    Route::post('/{image}/generate-happy', [App\Http\Controllers\ImageController::class, 'generateHappy'])->name('generate-happy');
    Route::post('/{image}/generate-sad', [App\Http\Controllers\ImageController::class, 'generateSad'])->name('generate-sad');
    Route::post('/bulk-action', [App\Http\Controllers\ImageController::class, 'bulkAction'])->name('bulk-action');
});

// Preview Routes
Route::get('/preview', [PreviewController::class, 'index'])->name('preview.kiosk');
