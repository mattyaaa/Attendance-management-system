<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Display the list of requests.
     */
    public function index(Request $request)
    {
    $userId = Auth::id();
    $currentTab = $request->query('tab', 'pending'); // デフォルトは 'pending'

    // データを取得
    $pendingRequests = [];
    $approvedRequests = [];

    if ($currentTab === 'pending') {
        $pendingRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    } elseif ($currentTab === 'approved') {
        $approvedRequests = AttendanceRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    return view('users.request_list', compact('pendingRequests', 'approvedRequests', 'currentTab'));
    }
}
