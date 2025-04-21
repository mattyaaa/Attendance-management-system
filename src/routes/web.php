<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'showRegisterForm'])->name('attendance.form');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/break_start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('/attendance/break_end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');