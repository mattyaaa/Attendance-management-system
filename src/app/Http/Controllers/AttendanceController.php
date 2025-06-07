<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceModificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
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

    public function details($date, Request $request)
{
    // クエリパラメータから user_id を取得（指定がない場合はログイン中のユーザー）
    $userId = $request->query('user_id', auth()->id());

    // ログイン中のユーザー情報を取得
    $authUser = auth()->user();

    // 権限チェック
    // role_id が 2 の場合は管理者、それ以外は一般ユーザー
    $isAdmin = $authUser->role_id === 2;

    // 管理者以外は自分自身のデータにのみアクセス可能
    if (!$isAdmin && $authUser->id != $userId) {
        abort(403, 'この操作は許可されていません。');
    }

    // 勤怠データを取得
    $attendance = Attendance::where('user_id', $userId)
        ->where('date', $date)
        ->first();

    // 勤怠データが見つからない場合
    if (!$attendance) {
        return redirect()->route('admin.attendance.list') // 適切なリスト画面にリダイレクト
            ->with('error', '指定された勤怠データが見つかりませんでした。');
    }
    // 休憩情報を取得（存在しない場合は空のコレクション）
    $breakTimes = $attendance ? $attendance->breakTimes : collect();

    // 修正申請のステータスを取得（存在しない場合は not_requested）
    $attendanceRequest = $attendance
        ? AttendanceRequest::where('attendance_id', $attendance->id)->first()
        : null;

    $status = $attendanceRequest->status ?? 'not_requested';

    // 使用するレイアウトを決定
    $layout = $isAdmin ? 'layouts.admin' : 'layouts.app';

    if ($isAdmin) {
        return view('admin.attendance_details', [
            'attendance' => $attendance,
            'breakTimes' => $breakTimes,
            'date' => $date,
        ]);
    }

    return view('users.attendance_details', [
        'attendance' => $attendance,
        'breakTimes' => $breakTimes,
        'date' => $date,
        'status' => $status,
        'isAdmin' => $isAdmin,
    ]);
}

public function update(AttendanceModificationRequest $request, $date)
{
    // 対象の勤怠データを取得
    $attendance = Attendance::where('date', $date)
        ->where('user_id', $request->input('user_id'))
        ->firstOrFail();

    // 管理者のみがこのメソッドを利用可能
    $this->authorize('update', $attendance);

    // バリデーション済みデータを取得
    $validated = $request->validated();

    // 勤怠データを更新
    $updateData = [];
    if (!empty($validated['time_in'])) {
        $updateData['time_in'] = $validated['time_in'];
    }
    if (!empty($validated['time_out'])) {
        $updateData['time_out'] = $validated['time_out'];
    }
    if (!empty($validated['remarks'])) {
        $updateData['remarks'] = $validated['remarks'];
    }

    $attendance->update($updateData);

   // 休憩データを更新（空白の部分を無視して処理）
if (!empty($validated['breaks'])) {
    // 古いデータを削除
    BreakTime::where('attendance_id', $attendance->id)->delete();

    foreach ($validated['breaks'] as $break) {
        // 空白データをスキップ（break_in または break_out が空なら処理しない）
        if (empty($break['break_in']) || empty($break['break_out'])) {
            continue; // スキップして次のデータに進む
        }

        // 新しいデータを挿入
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => $break['break_in'],
            'break_out' => $break['break_out'],
        ]);
    }
}

     // 修正申請データが存在する場合、承認済みに変更
        $attendanceRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();
        if ($attendanceRequest) {
            $attendanceRequest->update([
                'status' => 'approved',
                'remarks' => '管理者が修正を承認し、直接修正を行いました。',
            ]);
        }

    return redirect()->route('admin.attendance.details', ['date' => $date])
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
        $attendanceRequest = AttendanceRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'time_in' => $request->input('time_in'),
            'time_out' => $request->input('time_out'),
            'remarks' => $request->input('remarks'),
            'status' => 'pending', // ステータスを「承認待ち」に設定
        ]);
        // 休憩時間の処理
        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            if (!empty($break['break_in']) && !empty($break['break_out'])) {
                BreakTime::create([
                    'attendance_request_id' => $attendanceRequest->id, 
                    'break_in' => $break['break_in'],
                    'break_out' => $break['break_out'],
                ]);
            }
        }


        return redirect()->route('attendance.details', ['date' => $attendance->date])
            ->with('success', '修正申請を送信しました！');
    }
    /**
     * スタッフ別勤怠データ一覧を表示
     *
     * @param int $id スタッフのID
     * @return \Illuminate\View\View
     */
    public function showByStaff($id, Request $request)
{
    // スタッフ情報を取得
    $staff = User::findOrFail($id);

    // 現在の月を取得（クエリパラメータ 'month' が指定されていればその月を使用）
    $currentMonth = $request->query('month', now()->format('Y-m'));

    // スタッフの指定された月の勤怠データを取得
    $attendances = Attendance::where('user_id', $id)
                             ->whereBetween('date', [
                                 \Carbon\Carbon::parse($currentMonth)->startOfMonth(),
                                 \Carbon\Carbon::parse($currentMonth)->endOfMonth(),
                             ])
                             ->orderBy('date', 'asc')
                             ->get();

    // ビューにデータを渡す
    return view('admin.staff_attendance_list', compact('staff', 'attendances', 'currentMonth'));
}
public function staffAttendance($id, Request $request)
{
    $currentMonth = $request->query('month', now()->format('Y-m'));
    $staff = User::findOrFail($id);

    // 指定されたスタッフの勤怠データを取得
    $attendances = Attendance::where('user_id', $id)
        ->whereBetween('date', [
            \Carbon\Carbon::parse($currentMonth)->startOfMonth(),
            \Carbon\Carbon::parse($currentMonth)->endOfMonth(),
        ])
        ->orderBy('date', 'asc')
        ->get();

    // 現在の月の日付を取得
    $currentDate = now(); // またはリクエストから取得

    return view('admin.staff_attendance_list', compact('staff', 'attendances', 'currentMonth', 'currentDate'));
}
}
