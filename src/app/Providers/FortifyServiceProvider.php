<?php

namespace App\Providers;

use App\Http\Controllers\Auth\CustomAuthenticatedSessionController;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;
use App\Http\Requests\AdminLoginRequest;
use App\Models\User;

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

        Fortify::authenticateUsing(function (Request $request) {
        // 管理者ログインの場合
        if ($request->is('admin/login')) {
            return $this->authenticateAdmin($request);
        }

        // 一般ユーザーログインの場合
        return $this->authenticateUser($request);
        });

        // ログインコントローラーをカスタムコントローラーに差し替え
        $this->app->bind(
            \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class,
            CustomAuthenticatedSessionController::class
        );

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

    //管理者認証
    protected function authenticateAdmin(Request $request)
    {
        $validated = app(AdminLoginRequest::class)->validated(); // AdminLoginRequest を使用
        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password) && $user->role_id === 2) {
        return $user; // 管理者認証成功
        }

        return null; // 管理者認証失敗
    }

    //一般ユーザー認証
    protected function authenticateUser(Request $request)
    {
        $validated = app(LoginRequest::class)->validated(); // LoginRequest を使用
        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password) && $user->role_id === 1) {
            return $user; // 一般ユーザー認証成功
        }

        return null; // 一般ユーザー認証失敗
    }
}