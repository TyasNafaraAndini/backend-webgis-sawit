<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PekerjaController;
use App\Http\Controllers\UploadPetaController;
use App\Http\Controllers\BlokController;
use App\Http\Controllers\AlatController;
use App\Http\Controllers\IrigasiController;
use App\Http\Controllers\JalanController;
use App\Http\Controllers\TransportasiController;
use App\Http\Controllers\LahanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', action: [AuthController::class, 'login']);
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

// Route Upload Peta
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/upload_peta', [UploadPetaController::class, 'index']);
    Route::get('/upload_peta/{id}', [UploadPetaController::class, 'show']);
    Route::post('/upload_peta', [UploadPetaController::class, 'store']);
    Route::put('/upload_peta/{id}', [UploadPetaController::class, 'update']);
    Route::delete('/upload_peta/{id}', [UploadPetaController::class, 'destroy']);
});

// Route Blok
Route::prefix('blok')->group(function () {
    Route::get('/', [BlokController::class, 'index']);         // list + filter
    Route::post('/', [BlokController::class, 'store']);        // tambah
    Route::get('/{id}', [BlokController::class, 'show']);      // detail
    Route::put('/{id}', [BlokController::class, 'update']);    // update
    Route::delete('/{id}', [BlokController::class, 'destroy']); // hapus
});

// Route Alat
Route::prefix('alat')->group(function () {
    Route::get('/', [AlatController::class, 'index']);       // list
    Route::post('/', [AlatController::class, 'store']);      // tambah
    Route::get('/{id}', [AlatController::class, 'show']);    // detail
    Route::put('/{id}', [AlatController::class, 'update']);  // update
    Route::delete('/{id}', [AlatController::class, 'destroy']); // hapus
});

// Route Irigasi
Route::prefix('irigasi')->group(function () {
    Route::get('/', [IrigasiController::class, 'index']);       // list + filter
    Route::post('/', [IrigasiController::class, 'store']);      // tambah
    Route::get('/{id}', [IrigasiController::class, 'show']);    // detail
    Route::put('/{id}', [IrigasiController::class, 'update']);  // update
    Route::delete('/{id}', [IrigasiController::class, 'destroy']); // hapus
});

// Route Jalan

Route::prefix('jalan')->group(function () {
    Route::get('/', [JalanController::class, 'index']);         // list + filter
    Route::post('/', [JalanController::class, 'store']);        // tambah
    Route::get('/{id}', [JalanController::class, 'show']);      // detail
    Route::put('/{id}', [JalanController::class, 'update']);    // update
    Route::delete('/{id}', [JalanController::class, 'destroy']); // hapus
});

// Route Transportasi
Route::prefix('transportasi')->group(function () {
    Route::get('/', [TransportasiController::class, 'index']);
    Route::post('/', [TransportasiController::class, 'store']);
    Route::get('/{id}', [TransportasiController::class, 'show']);
    Route::put('/{id}', [TransportasiController::class, 'update']);
    Route::delete('/{id}', [TransportasiController::class, 'destroy']);
});

// Route Lahan
Route::prefix('lahan')->group(function () {
    Route::get('/', [LahanController::class, 'index']);       // Ambil semua data lahan
    Route::post('/', [LahanController::class, 'store']);      // Tambah data lahan
    Route::get('/{id}', [LahanController::class, 'show']);    // Detail data lahan berdasarkan ID
    Route::put('/{id}', [LahanController::class, 'update']);  // Update data lahan
    Route::delete('/{id}', [LahanController::class, 'destroy']); // Hapus data lahan
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
//     $request->user()->currentAccessToken()->delete();

//     return response()->json(['message' => 'Logged out successfully']);
// });
