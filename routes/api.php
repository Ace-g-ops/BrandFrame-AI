<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
    Route::post('/gemerate', [GenerationController::class, 'generate'])->name('generate');
    Route::get('/generations', [GenerationController::class, 'index']);
    Route::get('generation/{id}', [GenerationController::class, 'delete'])->name('destroy');

});

// test routes
 Route::post('test-bria', [testBriaController::class, 'testGenerate'])->name('test-bria')->auth::sanctum();
