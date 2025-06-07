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

    // 承認・却下アクション
    public function approveAction(Request $request, $attendance_correct_request)
    {
        $attendanceRequest = AttendanceRequest::findOrFail($attendance_correct_request);

        if ($request->input('action') === 'approve') {
            $attendanceRequest->status = 'approved';
        } elseif ($request->input('action') === 'reject') {
            $attendanceRequest->status = 'rejected';
        }

        $attendanceRequest->save();

        return redirect()->route('admin.request.list', ['tab' => 'pending'])
                         ->with('success', '申請を更新しました。');
    }
}
