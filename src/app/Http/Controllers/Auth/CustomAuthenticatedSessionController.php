<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


class CustomAuthenticatedSessionController extends AuthenticatedSessionController
{
    /**
     * Redirect users after login based on their role.
     */
    protected function redirectTo(Request $request)
    {
        // 現在認証されているユーザーを取得
        $user = Auth::user();
        \Log::info('Redirecting user', ['user_id' => $user->id, 'role_id' => $user->role_id]);

        // 管理者の場合
        if ($user->role_id === 2) { // role_idが2の場合を管理者と仮定
            return '/admin/attendance/list'; // 管理者専用のリダイレクト先
        }

        // 一般ユーザーの場合
        return '/attendance'; // 一般ユーザー専用のリダイレクト先
    }
}