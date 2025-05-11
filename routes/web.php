<?php

use App\Http\Middleware\UserAccess;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceHistory;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Controllers\CameraAttedanceController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [AuthController::class, 'index'])->middleware(RedirectIfAuthenticated::class)->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(RedirectIfAuthenticated::class);
Route::get('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth', UserAccess::class . ':admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index']);
    Route::resource('/manage-user', ManageUserController::class);
    Route::post('/import', [ImportExcelController::class, 'import'])->name('import.excel');
    Route::resource('/manage-location', LocationController::class);
    Route::get('/attendance-history', [AttendanceHistory::class, 'attendanceHistory'])->name('user');
    Route::get('/siswa', [ManageUserController::class, 'siswa'])->name('siswa.index');
});
Route::middleware(['auth', UserAccess::class . ':user'])->group(function () {
    Route::get('/index', [UserController::class, 'index'])->name('user.index');
    Route::get('/riwayat-absen', [UserController::class, 'riwayatAbsen'])->name('user.riwayat');
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/absen', [UserController::class, 'store'])->name('user.store');
});

// Route untuk redirect ke provider OAuth
Route::get('auth/{provider}', [App\Http\Controllers\AuthController::class, 'redirectToProvider']);

// Route untuk callback dari provider OAuth
Route::get('auth/{provider}/callback', [App\Http\Controllers\AuthController::class, 'handleProviderCallback']);

// camera
Route::post('/absensi-camera', [UserController::class, 'cameraAbsensi'])->name('user.cameraAbsensi');
