<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RequestController extends Controller
{
    public function index(Request $request)
    {
               // 認証済みユーザーを取得
        $user = Auth::user();

        // 現在のタブを取得（デフォルトは 'pending'）
        $currentTab = $request->query('tab', 'pending');

        if ($user->role_id === 2) { // 管理者
            $pendingRequests = AttendanceRequest::where('status', 'pending')
                ->with('user') // リレーションを使用してユーザー情報を取得
                ->orderBy('created_at', 'desc')
                ->get();

            $approvedRequests = AttendanceRequest::where('status', 'approved')
                ->with('user') // リレーションを使用してユーザー情報を取得
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.request_list', compact('pendingRequests', 'approvedRequests', 'currentTab'));
        }

        if ($user->role_id === 1) { // 一般ユーザー
            $pendingRequests = AttendanceRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            $approvedRequests = AttendanceRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('users.request_list', compact('pendingRequests', 'approvedRequests', 'currentTab'));
        }

        // ロールが不明な場合は403エラー
        abort(403, 'Unauthorized access');
    }
}