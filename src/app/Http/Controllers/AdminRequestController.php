<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;


class AdminRequestController extends Controller
{
    // 修正申請承認画面
    public function approve($attendance_correct_request)
    {
        $request = AttendanceRequest::with(['user', 'breaks'])->findOrFail($attendance_correct_request);
        return view('admin.request_approval', compact('request'));
    }

    public function approveAction(Request $request, $attendance_correct_request)
{
    DB::beginTransaction();
    try {
        $attendanceRequest = AttendanceRequest::with('breaks')->findOrFail($attendance_correct_request);

        if ($request->input('action') === 'approve') {
            $attendanceRequest->status = 'approved';
            $attendanceRequest->save();

            // 勤怠データも申請内容で更新
            $attendance = Attendance::find($attendanceRequest->attendance_id);
            if ($attendance) {
                $attendance->time_in = $attendanceRequest->time_in;
                $attendance->time_out = $attendanceRequest->time_out;
                $attendance->save();

                // 休憩データも申請内容で反映
                BreakTime::where('attendance_id', $attendance->id)->delete();
                if ($attendanceRequest->breaks && $attendanceRequest->breaks->count()) {
                    foreach ($attendanceRequest->breaks as $break) {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_in' => $break->break_in,
                            'break_out' => $break->break_out,
                        ]);
                    }
                }
            }
        } elseif ($request->input('action') === 'reject') {
            $attendanceRequest->status = 'rejected';
            $attendanceRequest->save();
        }

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json(['status' => $attendanceRequest->status]);
        }

        return redirect()->route('admin.request.approve', [
            'attendance_correct_request' => $attendance_correct_request
        ])->with('success', '申請を更新しました。');
    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
        throw $e;
    }
}
}
