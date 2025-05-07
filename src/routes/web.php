<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;

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

// 管理者専用ルート
Route::middleware(['auth', 'can:admin'])->group(function () {
    // 管理者用 勤怠リスト画面の表示
    Route::get('/admin/attendance/list', function () {
        return view('admin.attendance_list');
    })->name('admin.attendance.list');
});

// 一般ユーザーのログイン
Route::get('/login', function () {
    return view('users.login');
})->name('login');

// 一般ユーザーのログイン処理 (POST)
Route::post('/login', [CustomAuthenticatedSessionController::class, 'store'])
    ->middleware(['guest'])
    ->name('login.post');

// 管理者のログイン
Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');

// 管理者ログイン処理 (POST)
Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store'])
    ->middleware(['guest'])
    ->name('admin.login.post');

// 一般ユーザーのログアウト
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login'); // 一般ログインページにリダイレクト
})->name('logout');

// 管理者用のログアウト
Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect('/admin/login'); // 管理者ログインページにリダイレクト
})->name('admin.logout');