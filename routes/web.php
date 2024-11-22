<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\IticController;
use App\Http\Controllers\Auth\YpareoController;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\Hotspot\StaffController;
use App\Http\Controllers\Hotspot\StudentController;
use App\Http\Controllers\Marking\CriterionController;
use App\Http\Controllers\Marking\PointController;
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

Route::middleware(['guest'])->group(function () {
    // Auth
    Route::get('/auth/google', [GoogleController::class, 'showLogin'])->name('auth.google.showLogin');
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/ypareo', [YpareoController::class, 'showLogin'])->name('auth.ypareo.showLogin');
    Route::post('/auth/ypareo', [YpareoController::class, 'doLogin'])->name('auth.ypareo.doLogin');
    Route::get('/auth/itic', [IticController::class, 'showLogin'])->name('auth.itic.showLogin');
    Route::post('/auth/itic', [IticController::class, 'doLogin'])->name('auth.itic.doLogin');

    // Hotspot
    Route::get('/hotspot/login', [HotspotController::class, 'redirectToLogin'])->name('hotspot.redirectToLogin');
    Route::get('/hotspot/staff/callback', [StaffController::class, 'callback'])->name('hotspot.staff.callback');
    Route::get('/hotspot/students/callback', [StudentController::class, 'callback'])->name('hotspot.students.callback');
    Route::get('/hotspot/connected', [HotspotController::class, 'showConnected'])->name('hotspot.showConnected');
});

Route::middleware(['auth'])->group(function () {
    // Auth
    Route::get('/auth/logout', LogoutController::class)->name('auth.logout');

    // Marking
    Route::resource('marking.criteria', CriterionController::class);
    Route::resource('students.points', PointController::class)->shallow()->except(['show']);
});
