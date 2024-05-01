<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\adminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tambahAdmin', [adminController::class, 'tambah']);
Route::get('/dataAdmin', [adminController::class, 'data'])->name("dataAdmin.admin");;
Route::post('/tambahAdmin', [adminController::class, 'admin'])->name("tambahAdmin.admin");
Route::get('/deleteAdmin/{username}', [AdminController::class, 'delete'])->name('deleteAdmin.delete');

// Route::get('/daftar', [registerController::class, 'store']);
