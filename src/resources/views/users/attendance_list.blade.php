@extends('layouts.app')

@section('content')
<div class="container">
    <h1>勤怠一覧</h1>
    <div class="mb-3">
        <form method="GET" action="{{ route('attendance.list') }}">
            <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" class="btn btn-secondary">前月</button>
            <span class="mx-3">{{ \Carbon\Carbon::parse($currentMonth)->format('Y年m月') }}</span>
            <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" class="btn btn-secondary">翌月</button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @php
                // 現在の月の開始日と終了日を計算
                $startOfMonth = \Carbon\Carbon::parse($currentMonth)->startOfMonth();
                $endOfMonth = \Carbon\Carbon::parse($currentMonth)->endOfMonth();

                // 日付ごとの勤怠情報を取得しやすいようにリスト化
                $attendanceMap = $attendances->keyBy('date');
            @endphp

            @for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay())
                <tr>
                    @php
                        // 現在の日付に対応する勤怠データを取得
                        $attendance = $attendanceMap->get($date->toDateString());
                        $breakInstance = $attendance ? \App\Models\BreakTime::where('user_id', $attendance->user_id)
                            ->where('date', $attendance->date)
                            ->first() : null;

                        $totalBreakTime = 0;

                        if ($breakInstance) {
                            // 1回目の休憩時間を計算
                            if ($breakInstance->break_in_1 && $breakInstance->break_out_1) {
                                $totalBreakTime += \Carbon\Carbon::parse($breakInstance->break_in_1)
                                    ->diffInMinutes(\Carbon\Carbon::parse($breakInstance->break_out_1));
                            }

                            // 2回目の休憩時間を計算
                            if ($breakInstance->break_in_2 && $breakInstance->break_out_2) {
                                $totalBreakTime += \Carbon\Carbon::parse($breakInstance->break_in_2)
                                    ->diffInMinutes(\Carbon\Carbon::parse($breakInstance->break_out_2));
                            }
                        }

                        // 合計時間の計算
                        $timeIn = $attendance ? \Carbon\Carbon::parse($attendance->time_in) : null;
                        $timeOut = $attendance ? \Carbon\Carbon::parse($attendance->time_out) : null;

                        $totalMinutes = ($timeIn && $timeOut) 
                            ? $timeIn->diffInMinutes($timeOut) - $totalBreakTime 
                            : 0;
                    @endphp

                    <!-- 日付 -->
                    <td>{{ $date->format('m/d') }}（{{ $date->isoFormat('ddd') }}）</td>

                    <!-- 出勤時間 -->
                    <td>{{ $attendance && $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '' }}</td>

                    <!-- 退勤時間 -->
                    <td>{{ $attendance && $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '' }}</td>

                    <!-- 休憩時間 -->
                    <td>
                        {{ $totalBreakTime > 0 ? sprintf('%02d:%02d', intdiv($totalBreakTime, 60), $totalBreakTime % 60) : '' }}
                    </td>

                    <!-- 合計時間 -->
                    <td>
                        {{ $totalMinutes > 0 ? sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60) : '' }}
                    </td>

                    <!-- 詳細 -->
                    <td>
                        @if ($attendance)
                            <a href="{{ route('attendance.details', ['date' => $attendance->date]) }}" class="btn btn-primary">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@endsection