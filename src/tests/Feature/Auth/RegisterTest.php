<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function 名前が未入力の場合はエラーメッセージが表示される()
    {
        $response = $this->post('/register', [
            'username' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['username']);
    }

    /** @test */
    public function メールアドレスが未入力の場合はエラーメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワードが8文字未満の場合はエラーメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    public function パスワードが一致しない場合はエラーメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    public function パスワードが未入力の場合はエラーメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function フォームが正しければユーザーが正常に保存される()
    {
        $response = $this->post('/register', [
            'username' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->dump();

        // DBにユーザーが保存されているか確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }
}