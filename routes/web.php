<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\IticController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\OneButtonController;
use App\Http\Controllers\Auth\YpareoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Exam\AssignmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HotspotController;
use App\Http\Controllers\Hotspot\StaffController;
use App\Http\Controllers\Hotspot\StudentController;
use App\Http\Controllers\Marking\CriterionController;
use App\Http\Controllers\Marking\PointController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\VerifySebIntegrity;
use CFPropertyList\CFPropertyList;
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

Route::middleware(['guest'])->group(function () {
    Route::view('/', 'welcome');

    // Auth
    Route::get('/auth/1button', [OneButtonController::class, 'showLogin'])->name('auth.1button.showLogin');
    Route::post('/auth/1button', [OneButtonController::class, 'doLogin'])->name('auth.1button.doLogin');
    Route::get('/auth/google', [GoogleController::class, 'showLogin'])->name('auth.google.showLogin');
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
    Route::get('/auth/ypareo', [YpareoController::class, 'showLogin'])->name('auth.ypareo.showLogin');
    Route::post('/auth/ypareo', [YpareoController::class, 'doLogin'])->name('auth.ypareo.doLogin');
    Route::get('/auth/itic', [IticController::class, 'showLogin'])->name('auth.itic.showLogin');
    Route::post('/auth/itic', [IticController::class, 'doLogin'])->name('auth.itic.doLogin');
    Route::post('/auth/itic/password-reset', [IticController::class, 'sendPasswordReset'])->name('auth.itic.sendPasswordReset');
    Route::middleware(['signed'])->group(function () {
        Route::get('/auth/itic/password-reset', [IticController::class, 'showPasswordReset'])->name('auth.itic.showPasswordReset');
        Route::patch('/auth/itic/password-reset', [IticController::class, 'doPasswordReset'])->name('auth.itic.doPasswordReset');
    });

    // Hotspot
    Route::get('/hotspot/login', [HotspotController::class, 'redirectToLogin'])->name('hotspot.redirectToLogin');
    Route::get('/hotspot/staff/callback', [StaffController::class, 'callback'])->name('hotspot.staff.callback');
    Route::get('/hotspot/students/callback', [StudentController::class, 'callback'])->name('hotspot.students.callback');
    Route::get('/hotspot/connected', [HotspotController::class, 'showConnected'])->name('hotspot.showConnected');
});

Route::middleware(['auth'])->group(function () {
    // Auth
    Route::get('/auth/logout', LogoutController::class)->name('auth.logout');

    // Common
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Trainings
    Route::resource('trainings', TrainingController::class)->only(['index', 'show']);
    Route::get('/trainings/{training}/points/create', [PointController::class, 'createBatch'])->name('trainings.points.create');
    Route::post('/trainings/{training}/points', [PointController::class, 'storeBatch'])->name('trainings.points.store');

    // Marking
    Route::group([
        'prefix' => '/marking',
        'as' => 'marking/',
    ], function () {
        Route::resource('criteria', CriterionController::class);
    });
    Route::resource('students.points', PointController::class)->shallow()->except(['show']);

    // Users
    Route::patch('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
    Route::resource('users', UserController::class);

    // Assignments (Students)
    Route::get('/exams/assignments', [AssignmentController::class, 'index'])->name('exams.assignments.index');
    Route::get('/exams/assignments/{assignment:uuid}', [AssignmentController::class, 'show'])->name('exams.assignments.show');
    Route::get('/exams/assignments/{assignment:uuid}/start', [AssignmentController::class, 'start'])->name('exams.assignments.start')->middleware(VerifySebIntegrity::class);
    Route::get('/exams/assignments/{assignment:uuid}/finish', [AssignmentController::class, 'finish'])->name('exams.assignments.finish');
    Route::get('/exams/assignments/{assignment:uuid}/pass/{question}', [AssignmentController::class, 'pass'])->name('exams.assignments.pass')->middleware(VerifySebIntegrity::class);
    Route::post('/exams/assignments/{assignment:uuid}/pass/{question}', [AssignmentController::class, 'answer'])->name('exams.assignments.answer')->middleware(VerifySebIntegrity::class);

    // Exams (Trainer)
    Route::get('/exams/{group_uuid}/report', [ExamController::class, 'downloadReport'])->name('exams.report');
    Route::get('/exams/{exam}/assign', [ExamController::class, 'showAssign'])->name('exams.showAssign');
    Route::post('/exams/{exam}/assign', [ExamController::class, 'doAssign'])->name('exams.doAssign');
    Route::get('/exams/{exam}/self-assign', [ExamController::class, 'selfAssign'])->name('exams.self-assign');
    Route::resource('exams', ExamController::class);
});

Route::get('/trainings/{training}/ranking', [TrainingController::class, 'ranking'])->name('trainings.ranking');
