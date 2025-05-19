<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class Adminstaffcontroller extends Controller
{
    /**
     * スタッフ一覧画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 一般ユーザーの「氏名」と「メールアドレス」を取得
        $staffs = User::where('role_id', 1)
                      ->select('id', 'name', 'email') // 必要なカラムのみ取得
                      ->get();

        return view('admin.staff_list', compact('staffs'));
    }
}
