<?php

use App\Http\Controllers\adminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\MakananController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\ProfileController;



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


Route::post('/daftar', [registerController::class, 'store']);

Route::post('/getDataUser', [loginController::class, 'getData']);

Route::post('/image', [adminController::class, 'uploudimage']);



Route::post('/create_makanan', [MakananController::class, 'create_makanan']);
Route::get('/makanan/search_makanan', [MakananController::class, 'search_makanan']);
Route::get('/makanan/search_makanan_barcode', [MakananController::class, 'search_makanan_barcode']);

Route::post('/create_artikel_makanan', [ArtikelController::class, 'create_artikel_makanan']);
Route::post('/update_artikel_makanan', [ArtikelController::class, 'update_artikel_makanan']);
Route::post('/delete_artikel_makanan', [ArtikelController::class, 'delete_artikel_makanan']);
Route::get('/read_semua_artikel_makanan', [ArtikelController::class, 'read_semua_artikel_makanan']);

Route::post('/image', [adminController::class, 'uploudimage']);
Route::post('/create_update_profile_diri', [ProfileController::class, 'create_update_profile_diri']);
Route::get('/read_profile_by_email', [ProfileController::class, 'read_profile_by_email']);
Route::get('/read_kebutuhan_gizi_by_email', [ProfileController::class, 'read_kebutuhan_gizi_by_email']);
