<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 会員登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // 一般ユーザーの会員登録画面
        Fortify::registerView(function () {
            return view('users.register');
        });

        // ログイン画面の切り替え (一般ユーザーと管理者)
        Fortify::loginView(function (Request $request) {
            // URLが '/admin/login' の場合は管理者ログインビューを返す
            if ($request->is('admin/login')) {
                return view('admin.login');
            }

            // それ以外は一般ユーザーのログインビューを返す
            return view('users.login');
        });

        // 一般ユーザーのRate Limiter
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // 管理者用のRate Limiter
        RateLimiter::for('admin-login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });
    }
}