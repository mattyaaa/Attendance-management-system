<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('email', [
            'tanaka@example.com',
            'sato@example.com',
        ])->get();

        $start = Carbon::create(2025, 4, 1);
        $end = Carbon::create(2025, 6, 7);

        foreach ($users as $user) {
            // まず平日一覧を作成
            $weekdays = [];
            $date = $start->copy();
            while ($date->lte($end)) {
                if (!$date->isWeekend()) {
                    $weekdays[] = $date->toDateString();
                }
                $date->addDay();
            }

            // 平日からランダムで2～4日選ぶ（休みの日）
            $offDays = collect($weekdays)->random(rand(2, 4))->toArray();

            // 勤怠データ作成
            foreach ($weekdays as $wday) {
                if (in_array($wday, $offDays)) {
                    continue; // この日は休みなのでスキップ
                }

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $wday,
                    'time_in' => '09:00:00',
                    'time_out' => '18:00:00',
                    'status' => 'finished',
                ]);

                // お昼休憩
                $lunchIn = Carbon::createFromFormat('Y-m-d H:i:s', $wday . ' 12:00:00');
                $lunchOut = Carbon::createFromFormat('Y-m-d H:i:s', $wday . ' 13:00:00');
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_in' => $lunchIn->format('H:i:s'),
                    'break_out' => $lunchOut->format('H:i:s'),
                ]);

                // 他の休憩（ランダムで0～2回）
                $breakCount = rand(0, 2);
                $usedTimes = [
                    ['in' => $lunchIn, 'out' => $lunchOut]
                ];
                for ($i = 0; $i < $breakCount; $i++) {
                    // 午前または午後ランダム
                    if (rand(0, 1) === 0) {
                        $breakStartHour = rand(10, 11);
                        $breakStartMin = rand(0, 45);
                    } else {
                        $breakStartHour = rand(13, 16);
                        $breakStartMin = rand(15, 59);
                    }
                    $breakLength = rand(10, 30);

                    $breakIn = Carbon::createFromFormat('Y-m-d H:i:s', $wday . sprintf(' %02d:%02d:00', $breakStartHour, $breakStartMin));
                    $breakOut = $breakIn->copy()->addMinutes($breakLength);

                    // 他の休憩と被らないように
                    $overlap = false;
                    foreach ($usedTimes as $used) {
                        if (
                            ($breakIn->between($used['in'], $used['out'])) ||
                            ($breakOut->between($used['in'], $used['out'])) ||
                            ($used['in']->between($breakIn, $breakOut))
                        ) {
                            $overlap = true;
                            break;
                        }
                    }
                    if ($overlap) {
                        continue;
                    }
                    $usedTimes[] = ['in' => $breakIn, 'out' => $breakOut];

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_in' => $breakIn->format('H:i:s'),
                        'break_out' => $breakOut->format('H:i:s'),
                    ]);
                }
            }
        }
    }
}