<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;

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
    // 勤怠管理: 勤怠登録画面の表示
    Route::get('/attendance', [AttendanceController::class, 'showRegisterForm'])->name('attendance.form');

    // 勤怠管理: 出勤・退勤・休憩関連のアクション
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/break_start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('/attendance/break_end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list');
    // 勤怠詳細
    Route::get('/attendance/details/{date}', [AttendanceController::class, 'details'])->name('attendance.details');
    // 修正申請: 修正申請の作成
    Route::post('/attendance/request_modification/{attendanceId}', [AttendanceController::class, 'requestModification'])->name('attendance.requestModification');
    // 申請一覧
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('request.list');
});

// ログアウト処理
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');