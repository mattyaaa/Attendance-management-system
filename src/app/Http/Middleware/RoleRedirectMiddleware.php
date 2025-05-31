<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // ログイン中のユーザーを取得
        $user = Auth::user();

        // role_id に基づいて処理を分岐
        if ($user->role_id === 2) {
            // 管理者の場合
            $request->attributes->set('isAdmin', true);
        } else {
            // 一般ユーザーの場合
            $request->attributes->set('isAdmin', false);
        }

        return $next($request);
    }
}
