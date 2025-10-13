<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DainteeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PreviewController;

Route::get('/', function () {
    return redirect()->route('daintee.campaign');
});

// Daintee Campaign Routes
Route::get('/daintee', [DainteeController::class, 'index'])->name('daintee.campaign');
Route::post('/daintee/capture', [DainteeController::class, 'capture'])->name('daintee.capture');
Route::post('/daintee/process-first-selfie', [DainteeController::class, 'processFirstSelfie'])->name('daintee.process-first-selfie');
Route::post('/daintee/process-second-selfie', [DainteeController::class, 'processSecondSelfie'])->name('daintee.process-second-selfie');
Route::get('/daintee/image/{filename}', [DainteeController::class, 'getImage'])->name('daintee.image');
Route::get('/daintee/test-step4', [DainteeController::class, 'testStep4Data'])->name('daintee.test-step4');
Route::post('/daintee/check-job-status', [DainteeController::class, 'checkJobStatus'])->name('daintee.check-job-status');
Route::get('/daintee/processing-jobs', [DainteeController::class, 'getProcessingJobs'])->name('daintee.processing-jobs');

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
