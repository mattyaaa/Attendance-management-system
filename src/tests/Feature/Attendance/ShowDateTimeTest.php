<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function 勤怠打刻画面の現在日時がサーバー時刻と一致している()
    {
        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'user'
        ]);

        $user = User::factory()->create([
            'role_id' => 1
        ]);
        $this->actingAs($user);

        // サーバー側で「今」の曜日を日本語で得る
        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
        $now = Carbon::now();
        $currentDay = $weekDays[$now->dayOfWeek];

        // 日付部分（例：2025年6月11日（水））
        $expectedDate = $now->format('Y年n月j日') . '（' . $currentDay . '）';
        // 時刻部分（例：23:03）
        $expectedTime = $now->format('H:i');

        // 勤怠打刻画面へアクセス
        $response = $this->get('/attendance'); // ルートは適宜修正

        // 日付が含まれているか
        $response->assertSee($expectedDate);
        // 時刻が含まれているか
        $response->assertSee($expectedTime);
    }
}