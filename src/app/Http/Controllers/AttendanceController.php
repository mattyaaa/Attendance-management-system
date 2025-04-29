<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // 勤怠データを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->firstOrFail();

        // 新しい休憩レコードを作成
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => now(), // 休憩開始時間を記録
        ]);

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

        // 勤怠データを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->firstOrFail();

        // 最後の休憩レコードを取得
        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_out') // まだ終了していない休憩を取得
            ->latest('break_in') // 最新の休憩を取得
            ->firstOrFail();

        // 休憩終了時間を記録
        $breakTime->update([
            'break_out' => now(),
        ]);

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

    public function index(Request $request)
    {
        // 現在の月を取得
        $currentMonth = $request->query('month', Carbon::now()->format('Y-m'));

        // 月の開始日と終了日を計算
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // ログインユーザーの勤怠情報を取得
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        return view('users.attendance_list', [
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function details($date)
{
    $attendance = Attendance::where('date', $date)->where('user_id', auth()->id())->firstOrFail();
    $breakTimes = BreakTime::where('attendance_id', $attendance->id)->get();

    return view('users.attendance_details', [
        'attendance' => $attendance,
        'breakTimes' => $breakTimes,
        'date' => $date,
    ]);
}

public function update(AttendanceRequest $request, $date)
{
    $attendance = Attendance::where('date', $date)->where('user_id', auth()->id())->firstOrFail();
    $attendance->time_in = $request->input('time_in');
    $attendance->time_out = $request->input('time_out');
    $attendance->remarks = $request->input('remarks');
    $attendance->save();

    if ($request->has('breaks')) {
        foreach ($request->input('breaks') as $key => $break) {
            if (!empty($break['break_in']) && !empty($break['break_out'])) {
                BreakTime::updateOrCreate(
                    ['attendance_id' => $attendance->id, 'break_in' => $break['break_in']],
                    ['break_out' => $break['break_out']]
                );
            }
        }
    }

    return redirect()->route('attendance.details', ['date' => $date])
        ->with('success', '勤怠情報を更新しました！');
}
}
