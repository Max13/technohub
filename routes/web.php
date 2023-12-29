<?php

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

Route::get('/hotspot/login', [HotspotController::class, 'showLogin'])->name('hotspot.showLogin');

Route::get('/hotspot/ypareo/login', [YpareoController::class, 'showLogin'])->name('hotspot.ypareo.showLogin');
Route::post('/hotspot/ypareo/login', [YpareoController::class, 'doLogin'])->name('hotspot.ypareo.doLogin');
Route::view('/hotspot/ok', 'hotspot.connected', [
    'captive' => request()->captive,
    'dst' => request()->dst,
    'hs' => request()->hs,
    'mac' => request()->mac,
    'uptime' => request()->uptime,
]);
