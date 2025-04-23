<?php

namespace App\Http\Controllers;

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
    // ログインユーザーを取得
    $user = Auth::user();

    // 指定された日の勤怠情報を取得
    $attendance = Attendance::where('user_id', $user->id)
        ->where('date', $date)
        ->first();

    // 休憩情報を取得
    $breakTime = BreakTime::where('user_id', $user->id)
        ->where('date', $date)
        ->first();

    // 勤怠詳細画面を表示
    return view('users.attendance_details', [
        'attendance' => $attendance,
        'breakTime' => $breakTime,
        'date' => $date,
    ]);
}
public function update(Request $request, $date)
{
    $user = Auth::user();

    // 勤怠情報を取得または新規作成
    $attendance = Attendance::firstOrNew([
        'user_id' => $user->id,
        'date' => $date,
    ]);

    // 入力内容を保存
    $attendance->time_in = $request->input('time_in');
    $attendance->time_out = $request->input('time_out');
    $attendance->remarks = $request->input('remarks');
    $attendance->save();

    // 休憩情報を保存
    $breakTime = BreakTime::firstOrNew([
        'user_id' => $user->id,
        'date' => $date,
    ]);

    $breakTime->break_in_1 = $request->input('break_in_1');
    $breakTime->break_out_1 = $request->input('break_out_1');
    $breakTime->save();

    return redirect()->route('attendance.list')->with('success', '修正が完了しました。');
}
}