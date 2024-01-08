<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Hotspot\StaffController;
use App\Http\Controllers\Hotspot\YpareoController;
use App\Http\Controllers\HotspotController;
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

Route::get('/', function () {
    return view('welcome');
});

// Hotspot
Route::get('/hotspot/login', [HotspotController::class, 'redirectToLogin'])->name('hotspot.redirectToLogin');

Route::get('/auth/google', [GoogleController::class, 'showLogin'])->name('auth.google.showLogin');
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

Route::get('/hotspot/staff/callback', [StaffController::class, 'callback'])->name('hotspot.staff.callback');

Route::get('/hotspot/ypareo/login', [YpareoController::class, 'showLogin'])->name('hotspot.ypareo.showLogin');
Route::post('/hotspot/ypareo/login', [YpareoController::class, 'doLogin'])->name('hotspot.ypareo.doLogin');
Route::view('/hotspot/ok', 'hotspot.connected', [
    'captive' => request()->captive,
    'dst' => request()->dst,
    'hs' => request()->hs,
    'mac' => request()->mac,
    'uptime' => request()->uptime,
]);
// /Hotspot
