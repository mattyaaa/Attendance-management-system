<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User', // 管理者の名前
            'email' => 'admin@example.com', // 管理者のメールアドレス
            'password' => Hash::make('admin1234'), // 管理者のパスワード
            'role_id' => 2, // 管理者のロールID (2と仮定)
        ]);
    }
}
