<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\FailToBan;



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

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('fail2ban');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/signup', [App\Http\Controllers\SignupController::class, 'create'])->name('signup.create');
Route::post('/signup', [App\Http\Controllers\SignupController::class, 'store'])->name('signup.store');
