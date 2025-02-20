<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\kategoriController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\TanggapanController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register',[UserController::class, 'register']);
Route::post('/login',[UserController::class, 'login']);
Route::get('/get/users',[UserController::class, 'index']);
Route::get('/get/lokasi', [LokasiController::class, 'index']);
Route::get('/get/kategori', [kategoriController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){

    Route::post('/logout',[UserController::class, 'logout']);

    //user
    Route::post('/update/user/{id}', [UserController::class, 'updateUserById']);
    
    //tambah/hapus kategori
    Route::post('/add/kategori', [kategoriController::class, 'addKategori']);
    route::delete('/delete/kategori/{id}', [kategoriController::class, 'deleteKategoriById']);
    
    //tambah/hapus kategori
    Route::post('/add/lokasi', [LokasiController::class, 'addLokasi']);
    Route::delete('/delete/lokasi/{id}', [LokasiController::class, 'deleteLokasiById']);

    //pengaduan
    Route::post('/add/pengaduan', [PengaduanController::class, 'addPengaduan']);
    Route::post('/status/pengaduan/{id}', [PengaduanController::class, 'updateStatusPengaduan']);
    Route::get('/get/pengaduan', [PengaduanController::class, 'index']);
    Route::get('/get/pengaduan/{id}', [PengaduanController::class, 'getPengaduanById']);
    Route::get('/pengaduan/user', [PengaduanController::class, 'getPengaduanByUserId']);
    Route::delete('/delete/pengaduan/{id}', [PengaduanController::class, 'deletePengaduanById']);
    
    //Tanggapan
    Route::post('/add/tanggapan/{pengaduanid}', [TanggapanController::class, 'addTanggapan']);
    Route::get('/get/{id}/tanggapan', [TanggapanController::class, 'getTanggapanByPengaduanId']);
    Route::get('/get/tanggapan', [TanggapanController::class, 'index']);
});
