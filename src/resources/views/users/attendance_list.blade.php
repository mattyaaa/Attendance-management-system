@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/users/attendance_list.css') }}">
@endsection

@section('content')
<div class="container attendance-list-container">
    <h1 class="attendance-list-title">勤怠一覧</h1>

    <!-- 月切り替え部分 -->
    <div class="month-navigation mb-3">
    <!-- 前月ボタン -->
    <form method="GET" action="{{ route('attendance.list') }}" class="previous-month">
    @csrf
    <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" class="btn btn-secondary">
        <img src="/images/arrow.png" alt="左矢印" class="arrow-icon"> 前月
    </button>
</form>

    <!-- 現在の月 -->
     <img src="/images/calendar.png" alt="カレンダー" class="month-icon">
    {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}

    <!-- 翌月ボタン -->
    <form method="GET" action="{{ route('attendance.list') }}" class="next-month">
    @csrf
    <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" class="btn btn-secondary">
        翌月 <img src="/images/arrow.png" alt="右矢印" class="arrow-icon flipped">
    </button>
</form>
</div>

    <!-- 勤怠テーブル部分 -->
    <div class="attendance-table-container">
        <table class="table attendance-table">
            <thead class="attendance-table-header">
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-table-body">
                @php
                    // 現在の月の開始日と終了日を計算
                    $startOfMonth = \Carbon\Carbon::parse($currentMonth)->startOfMonth();
                    $endOfMonth = \Carbon\Carbon::parse($currentMonth)->endOfMonth();

                    // 日付ごとの勤怠情報を取得しやすいようにリスト化
                    $attendanceMap = $attendances->keyBy('date');
                @endphp

                @for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay())
                    @php
                        // 現在の日付に対応する勤怠データを取得
                        $attendance = $attendanceMap->get($date->toDateString());

                        // 休憩時間の合計を計算
                        $totalBreakTime = 0;
                        if ($attendance) {
                            $breaks = $attendance->breakTimes; // リレーションを使用して取得
                            foreach ($breaks as $break) {
                                if ($break->break_in && $break->break_out) {
                                    $totalBreakTime += \Carbon\Carbon::parse($break->break_in)
                                        ->diffInMinutes(\Carbon\Carbon::parse($break->break_out));
                                }
                            }
                        }

                        // 勤務時間の計算
                        $timeIn = $attendance ? \Carbon\Carbon::parse($attendance->time_in) : null;
                        $timeOut = $attendance ? \Carbon\Carbon::parse($attendance->time_out) : null;

                        $totalMinutes = ($timeIn && $timeOut)
                            ? $timeIn->diffInMinutes($timeOut) - $totalBreakTime
                            : 0;
                    @endphp

                        <!-- 日付 -->
                        <td class="attendance-date">{{ $date->format('m/d') }}（{{ $date->isoFormat('ddd') }}）</td>

                        <!-- 出勤時間 -->
                        <td class="attendance-time-in">{{ $attendance && $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '' }}</td>

                        <!-- 退勤時間 -->
                        <td class="attendance-time-out">{{ $attendance && $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '' }}</td>

                        <!-- 休憩時間 -->
                        <td class="attendance-break">
                            {{ $totalBreakTime > 0 ? sprintf('%02d:%02d', intdiv($totalBreakTime, 60), $totalBreakTime % 60) : '' }}
                        </td>

                        <!-- 合計時間 -->
                        <td class="attendance-total">
                            {{ $totalMinutes > 0 ? sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60) : '' }}
                        </td>

                        <!-- 詳細 -->
                        <td class="attendance-details">
                            @if ($attendance)
                                <a href="{{ route('attendance.details', ['date' => $attendance->date]) }}" class="btn btn-primary">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection