<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PekerjaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route untuk admin
Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Halo Admin!']);
    });

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']); 
    Route::put('/users/{id}', [UserController::class, 'update']); 
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Route untuk staff
Route::middleware(['auth:sanctum', 'isStaff'])->get('/staff/dashboard', function () {
    return response()->json(['message' => 'Halo Staff!']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pekerja', [PekerjaController::class, 'index']);
    Route::get('/pekerja/{id}', [PekerjaController::class, 'show']);
    Route::post('/pekerja', [PekerjaController::class, 'store']);
    Route::put('/pekerja/{id}', [PekerjaController::class, 'update']);
    Route::delete('/pekerja/{id}', [PekerjaController::class, 'destroy']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
//     $request->user()->currentAccessToken()->delete();

//     return response()->json(['message' => 'Logged out successfully']);
// });
