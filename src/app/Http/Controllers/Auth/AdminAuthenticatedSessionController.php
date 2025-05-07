<?php

namespace App\Http\Controllers\Auth;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticatedSessionController extends AuthenticatedSessionController
{
    /**
     * Handle the login request for admins.
     */
    public function store(LoginRequest $request)
    {
    // Fortifyの既存認証処理を利用
    $response = parent::store($request);

    // 認証状態を確認
    if (Auth::check()) {
        $user = Auth::user();
        \Log::info('User authenticated', ['user_id' => $user->id, 'role_id' => $user->role_id]);
    } else {
        \Log::error('Authentication failed');
        return redirect('/admin/login')->withErrors(['email' => 'ログイン情報が登録されていません。']);
    }

    // ロールに基づきリダイレクト
    $user = Auth::user();
    if ($user->role_id !== 2) { // role_idが2を管理者と仮定
        Auth::logout(); // ログアウト
        return redirect('/admin/login')->withErrors(['email' => 'Unauthorized access.']);
    }

        return redirect('/admin/attendance/list'); // 管理者専用リダイレクト先
    }
}