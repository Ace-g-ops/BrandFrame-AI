<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandPresetController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\testBriaController;
use Laravel\Sanctum\Sanctum;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

 Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Image Upload
    Route::post('upload-product', [ImageController::class, 'uploadProduct'])->name('upload_product');

    //Generation Upload Routes
    Route::post('/generate', [GenerationController::class, 'generate']);
    Route::get('/generations', [GenerationController::class, 'index']);
    Route::get('/generation/{id}', [GenerationController::class, 'show'])->name('show');
    Route::delete('/generation/{id}', [GenerationController::class, 'destroy'])->name('dstroy');

});

// Brand Preset
Route::middleware('auth:sanctum')->group(function() {

    Route::get('/presets', [BrandPresetController::class, 'index']);
    Route::post('/presets', [BrandPresetController::class, 'store']);
    Route::get('/presets/{id}', [BrandPresetController::class, 'show']);
    Route::put('/presets/{id}', [BrandPresetController::class, 'update']);
    Route::delete('/presets/{id}', [BrandPresetController::class, 'destroy']);

});

// Apply To PreSET To Generate Route
Route::post('/apply-preset', [BrandPresetController::class, 'applyPreset']);

// test routes
 Route::post('test-bria', [testBriaController::class, 'testGenerate'])->name('test-bria')->middleware('auth::sanctum');
