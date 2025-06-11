<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        DB::table('roles')->insert(['id' => 2, 'name' => 'admin']);
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'role_id' => 2,
        ]);
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpass',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください。',
        ]);
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        DB::table('roles')->insert(['id' => 2, 'name' => 'admin']);
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'role_id' => 2,
        ]);
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください。',
        ]);
    }

    /** @test */
    public function 登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        DB::table('roles')->insert(['id' => 2, 'name' => 'admin']);
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'role_id' => 2,
        ]);
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'adminpass',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません。'
        ]);
    }
}