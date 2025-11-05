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
use App\Http\Controllers\ZonaController;
use App\Http\Controllers\PohonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================
// AUTHENTICATION
// ========================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ========================
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

// ========================
// PEKERJA
// ========================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pekerja', [PekerjaController::class, 'index']);         // GET: list pekerja
    Route::get('/pekerja/{id}', [PekerjaController::class, 'show']);     // GET: detail pekerja
    Route::post('/pekerja', [PekerjaController::class, 'store']);        // POST: tambah pekerja
    Route::put('/pekerja/{id}', [PekerjaController::class, 'update']);   // PUT: update pekerja
    Route::delete('/pekerja/{id}', [PekerjaController::class, 'destroy']); // DELETE: hapus pekerja
});

// ========================
// UPLOAD PETA
// ========================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/upload_peta', [UploadPetaController::class, 'index']);         // GET: list peta
    Route::get('/upload_peta/by-date', [UploadPetaController::class, 'getByDate']); // GET: filter by date
    Route::get('/upload_peta/{id}', [UploadPetaController::class, 'show']);     // GET: detail peta
    Route::post('/upload_peta', [UploadPetaController::class, 'store']);        // POST: tambah peta
    Route::put('/upload_peta/{id}', [UploadPetaController::class, 'update']);   // PUT: update peta
    Route::delete('/upload_peta/{id}', [UploadPetaController::class, 'destroy']); // DELETE: hapus peta
});

// ========================
// BLOK
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/blok', [BlokController::class, 'index']);                 // GET: list blok
Route::get('/blok/by-date', [BlokController::class, 'getByDate']);    // GET: filter by date
Route::get('/blok/{id}', [BlokController::class, 'show']);            // GET: detail blok
Route::post('/blok', [BlokController::class, 'store']);               // POST: tambah blok
Route::put('/blok/{id}', [BlokController::class, 'update']);          // PUT: update blok
Route::delete('/blok/{id}', [BlokController::class, 'destroy']);      // DELETE: hapus blok
});

// ========================
// ALAT
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/alat', [AlatController::class, 'index']);                // GET: list alat
Route::get('/alat/{id}', [AlatController::class, 'show']);            // GET: detail alat
Route::post('/alat', [AlatController::class, 'store']);               // POST: tambah alat
Route::put('/alat/{id}', [AlatController::class, 'update']);          // PUT: update alat
Route::delete('/alat/{id}', [AlatController::class, 'destroy']);      // DELETE: hapus alat
});

// ========================
// IRIGASI
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/irigasi', [IrigasiController::class, 'index']);          // GET: list irigasi
Route::get('/irigasi/by-date', [IrigasiController::class, 'getByDate']); // GET: filter by date
Route::get('/irigasi/{id}', [IrigasiController::class, 'show']);      // GET: detail irigasi
Route::post('/irigasi', [IrigasiController::class, 'store']);         // POST: tambah irigasi
Route::put('/irigasi/{id}', [IrigasiController::class, 'update']);    // PUT: update irigasi
Route::delete('/irigasi/{id}', [IrigasiController::class, 'destroy']); // DELETE: hapus irigasi
});

// ========================
// JALAN
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/jalan', [JalanController::class, 'index']);              // GET: list jalan
Route::get('/jalan/by-date', [JalanController::class, 'getByDate']);  // GET: filter by date
Route::get('/jalan/{id}', [JalanController::class, 'show']);          // GET: detail jalan
Route::post('/jalan', [JalanController::class, 'store']);             // POST: tambah jalan
Route::put('/jalan/{id}', [JalanController::class, 'update']);        // PUT: update jalan
Route::delete('/jalan/{id}', [JalanController::class, 'destroy']);    // DELETE: hapus jalan
});

// ========================
// TRANSPORTASI
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/transportasi', [TransportasiController::class, 'index']);       // GET: list transportasi
Route::get('/transportasi/{id}', [TransportasiController::class, 'show']);   // GET: detail transportasi
Route::post('/transportasi', [TransportasiController::class, 'store']);      // POST: tambah transportasi
Route::put('/transportasi/{id}', [TransportasiController::class, 'update']); // PUT: update transportasi
Route::delete('/transportasi/{id}', [TransportasiController::class, 'destroy']); // DELETE: hapus transportasi
});

// ========================
// LAHAN
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/lahan', [LahanController::class, 'index']);              // GET: list lahan
Route::get('/lahan/{id}', [LahanController::class, 'show']);          // GET: detail lahan
Route::post('/lahan', [LahanController::class, 'store']);             // POST: tambah lahan
Route::put('/lahan/{id}', [LahanController::class, 'update']);        // PUT: update lahan
Route::delete('/lahan/{id}', [LahanController::class, 'destroy']);    // DELETE: hapus lahan
});

// ========================
// ZONA
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/zona', [ZonaController::class, 'index']);                // GET: list zona
Route::get('/zona/by-date', [ZonaController::class, 'getByDate']);    // GET: filter by date
Route::get('/zona/{id}', [ZonaController::class, 'show']);            // GET: detail zona
Route::post('/zona', [ZonaController::class, 'store']);               // POST: tambah zona
Route::put('/zona/{id}', [ZonaController::class, 'update']);          // PUT: update zona
Route::delete('/zona/{id}', [ZonaController::class, 'destroy']);      // DELETE: hapus zona
});

// ========================
// POHON
// ========================
Route::middleware('auth:sanctum')->group(function () {
Route::get('/pohon/count', [PohonController::class, 'count']);
Route::get('/pohon', [PohonController::class, 'index']);              // GET: list pohon
Route::get('/pohon/{id}', [PohonController::class, 'show']);          // GET: detail pohon
Route::post('/pohon', [PohonController::class, 'store']);             // POST: tambah pohon
Route::put('/pohon/{id}', [PohonController::class, 'update']);        // PUT: update pohon
Route::delete('/pohon/{id}', [PohonController::class, 'destroy']);    // DELETE: hapus pohon
});
