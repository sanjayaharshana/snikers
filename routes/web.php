<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SnickersController;

Route::get('/', function () {
    return view('welcome');
});

// Snickers Campaign Routes
Route::get('/snickers', [SnickersController::class, 'index'])->name('snickers.campaign');
Route::post('/snickers/capture', [SnickersController::class, 'capture'])->name('snickers.capture');
Route::post('/snickers/process-first-selfie', [SnickersController::class, 'processFirstSelfie'])->name('snickers.process-first-selfie');
Route::get('/snickers/image/{filename}', [SnickersController::class, 'getImage'])->name('snickers.image');
