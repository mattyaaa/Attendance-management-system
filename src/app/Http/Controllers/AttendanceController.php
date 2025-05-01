<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceModificationRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
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
    $userId = auth()->id();

    // 出勤情報を取得
    $attendance = Attendance::where('user_id', $userId)
        ->where('date', $date)
        ->first();

    // 休憩情報を取得（存在しない場合は空のコレクション）
    $breakTimes = $attendance ? $attendance->breakTimes : collect();

    // 休憩情報を取得（存在しない場合は空のコレクション）
    $breakTimes = $attendance ? $attendance->breakTimes : collect();

    // 現在の修正申請のステータスを取得（デフォルトは not_requested）
    $attendanceRequest = $attendance
        ? AttendanceRequest::where('attendance_id', $attendance->id)->first()
        : null;

    $status = $attendanceRequest->status ?? 'not_requested';

    return view('users.attendance_details', [
        'attendance' => $attendance,
        'breakTimes' => $breakTimes,
        'date' => $date,
        'status' => $status, // 修正申請の現在のステータス
    ]);
}

public function update(Request $request, $date)
{
    // 管理者のみがこのメソッドを利用可能
    $this->authorize('update', Attendance::class);

    $validated = $request->validate([
        'time_in' => 'nullable|date_format:H:i',
        'time_out' => 'nullable|date_format:H:i',
        'breaks' => 'nullable|array',
        'remarks' => 'nullable|string|max:255',
    ]);

    // 対象の勤怠データを取得
    $attendance = Attendance::where('date', $date)
        ->where('user_id', $request->input('user_id'))
        ->firstOrFail();

    // 勤怠データを更新
    $attendance->update([
        'time_in' => $validated['time_in'],
        'time_out' => $validated['time_out'],
        'remarks' => $validated['remarks'],
        'breaks' => json_encode($validated['breaks']),
    ]);

    return redirect()->route('admin.modification_requests')
        ->with('success', '勤怠情報を更新しました！');
}

/**
     * 修正申請を作成する処理
     */
    public function requestModification(AttendanceModificationRequest $request, $attendanceId)
    {
        // 勤怠データを取得
        $attendance = Attendance::findOrFail($attendanceId);

        // 修正申請を作成
        AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'time_in' => $request->input('time_in'),
            'time_out' => $request->input('time_out'),
            'breaks' => json_encode($request->input('breaks')), // 休憩データをJSON形式で保存
            'remarks' => $request->input('remarks'),
            'status' => 'pending', // ステータスを「承認待ち」に設定
        ]);

        return redirect()->route('attendance.details', ['date' => $attendance->date])
            ->with('success', '修正申請を送信しました！');
    }
    public function createAttendance(Request $request)
{
    $userId = auth()->id();

    $validated = $request->validate([
        'date' => 'required|date|unique:attendances,date,NULL,id,user_id,' . $userId,
        'time_in' => 'required|date_format:H:i',
        'time_out' => 'nullable|date_format:H:i|after:time_in',
        'remarks' => 'nullable|string|max:255',
    ]);
}
}
