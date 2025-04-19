<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function showRegisterForm()
    {
        return view('users.attendance_register'); // 出勤登録画面のビュー
    }
}
