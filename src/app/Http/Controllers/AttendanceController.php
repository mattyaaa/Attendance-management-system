<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    /**
     * Show the attendance registration form.
     */
    public function showRegisterForm()
    {
        $user = auth()->user();
        $status = $this->getStatus($user);

        return view('users.attendance_register', compact('status'));
    }

    /**
     * Start working (出勤).
     */
    public function start(Request $request)
    {
        $user = auth()->user();

        // 出勤処理
        Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'time_in' => now(),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.form');
    }

    /**
     * Start break (休憩入).
     */
    public function breakStart(Request $request)
    {
        $user = auth()->user();

        // ステータス変更処理
        $this->updateStatus($user, 'break');

        // 休憩レコードの作成または更新
        $breakTime = BreakTime::firstOrCreate(
            ['user_id' => $user->id, 'date' => today()->toDateString()],
            ['break_in_1' => now()]
        );

        if (!$breakTime->break_in_1) {
            $breakTime->update(['break_in_1' => now()]);
        } elseif (!$breakTime->break_in_2) {
            $breakTime->update(['break_in_2' => now()]);
        }

        return redirect()->route('attendance.form');
    }

    /**
     * End break (休憩戻).
     */
    public function breakEnd(Request $request)
    {
        $user = auth()->user();

        // ステータス変更処理
        $this->updateStatus($user, 'working');

        // 休憩終了時間の記録
        $breakTime = BreakTime::where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->firstOrFail();

        if ($breakTime->break_in_1 && !$breakTime->break_out_1) {
            $breakTime->update(['break_out_1' => now()]);
        } elseif ($breakTime->break_in_2 && !$breakTime->break_out_2) {
            $breakTime->update(['break_out_2' => now()]);
        }

        return redirect()->route('attendance.form');
    }

    /**
     * End working (退勤).
     */
    public function end(Request $request)
    {
        $user = auth()->user();

        // 退勤処理
        $attendance = $this->getTodayAttendance($user);
        $attendance->update([
            'time_out' => now(),
            'status' => 'finished',
        ]);

        return redirect()->route('attendance.form');
    }

    /**
     * Get today's attendance record.
     */
    private function getTodayAttendance($user)
    {
        return Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->firstOrFail();
    }

    /**
     * Get the user's current status.
     */
    private function getStatus($user)
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        return $attendance->status ?? 'none'; // デフォルトは勤務外
    }

    /**
     * Update the status of the user's attendance record.
     */
    private function updateStatus($user, $status)
    {
        $attendance = $this->getTodayAttendance($user);
        $attendance->update(['status' => $status]);
    }
}