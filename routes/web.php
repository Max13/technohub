<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\YpareoController;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\Hotspot\StaffController;
use App\Http\Controllers\Hotspot\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'welcome');

// Auth
Route::get('/auth/google', [GoogleController::class, 'showLogin'])->name('auth.google.showLogin');
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/ypareo', [YpareoController::class, 'showLogin'])->name('auth.ypareo.showLogin');
Route::post('/auth/ypareo', [YpareoController::class, 'doLogin'])->name('auth.ypareo.doLogin');

// Hotspot
Route::get('/hotspot/login', [HotspotController::class, 'redirectToLogin'])->name('hotspot.redirectToLogin');
Route::get('/hotspot/staff/callback', [StaffController::class, 'callback'])->name('hotspot.staff.callback');
Route::get('/hotspot/students/callback', [StudentController::class, 'callback'])->name('hotspot.students.callback');
Route::get('/hotspot/connected', [HotspotController::class, 'showConnected'])->name('hotspot.showConnected');
Route::get('/hotspot/status', [HotspotController::class, 'showStatus'])->name('hotspot.showStatus');
