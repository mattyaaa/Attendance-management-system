<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        // 対象ユーザー
        $users = User::whereIn('email', [
            'tanaka@example.com',
            'sato@example.com',
        ])->get();

        foreach ($users as $user) {
            // ユーザーの勤怠からランダムに3件選んで修正申請を作成
            $attendances = Attendance::where('user_id', $user->id)->inRandomOrder()->limit(3)->get();

            foreach ($attendances as $attendance) {
                // 修正後の時刻
                $modifiedIn = Carbon::parse($attendance->time_in)->addMinutes(rand(-15, 15))->format('H:i:s');
                $modifiedOut = Carbon::parse($attendance->time_out)->addMinutes(rand(-15, 15))->format('H:i:s');

                // ステータスをランダムで
                $statuses = ['pending', 'approved'];
                $status = $statuses[array_rand($statuses)];

                // 申請理由をランダムで選択
                $remarksList = [
                    '電車遅延のため',
                    '体調不良のため',
                    '私用のため',
                    '通院のため',
                    '家庭の事情のため',
                    '道路渋滞のため遅刻',
                ];
                $remarks = $remarksList[array_rand($remarksList)];

                // 勤怠修正申請の作成
                $request = AttendanceRequest::create([
                    'user_id' => $user->id,
                    'attendance_id' => $attendance->id,
                    'date' => $attendance->date,
                    'time_in' => $modifiedIn,
                    'time_out' => $modifiedOut,
                    'remarks' => $remarks,
                    'status' => $status,
                ]);

                // 修正後の休憩（例：お昼は固定で他はランダム、2件程度）
                $lunchIn = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->date . ' 12:00:00')->addMinutes(rand(-5, 5));
                $lunchOut = Carbon::createFromFormat('Y-m-d H:i:s', $attendance->date . ' 13:00:00')->addMinutes(rand(-5, 5));

                // breaksテーブルに修正用休憩を挿入
                \DB::table('breaks')->insert([
                    [
                        'attendance_id' => null,
                        'attendance_request_id' => $request->id,
                        'break_in' => $lunchIn->format('H:i:s'),
                        'break_out' => $lunchOut->format('H:i:s'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'attendance_id' => null,
                        'attendance_request_id' => $request->id,
                        'break_in' => Carbon::createFromFormat('Y-m-d H:i:s', $attendance->date . ' 15:00:00')->addMinutes(rand(-10, 10))->format('H:i:s'),
                        'break_out' => Carbon::createFromFormat('Y-m-d H:i:s', $attendance->date . ' 15:20:00')->addMinutes(rand(-10, 10))->format('H:i:s'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }
    }
}