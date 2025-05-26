<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class AdminRequestController extends Controller
{
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'pending'); // デフォルトは 'pending'

        // データを取得
        $pendingRequests = [];
        $approvedRequests = [];

        if ($currentTab === 'pending') {
            $pendingRequests = AttendanceRequest::where('status', 'pending')
                ->with('user') // リレーションを使用してユーザー情報を取得
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($currentTab === 'approved') {
            $approvedRequests = AttendanceRequest::where('status', 'approved')
                ->with('user') // リレーションを使用してユーザー情報を取得
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.request_list', compact('pendingRequests', 'approvedRequests', 'currentTab'));
    }
}
