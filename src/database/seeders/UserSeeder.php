<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザー1
        User::create([
            'name' => '田中太郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('user1234'),
            'role_id' => 1,
        ]);

        // 一般ユーザー2
        User::create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('user5678'),
            'role_id' => 1,
        ]);
    }
}
