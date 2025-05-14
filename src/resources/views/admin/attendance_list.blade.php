@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endsection

@section('content')
<div class="container attendance-list-container">
    <h1 class="attendance-list-title">{{ \Carbon\Carbon::parse($currentDate)->translatedFormat('Y年n月j日') }}の勤怠</h1>

    <!-- 日付ナビゲーション -->
    <div class="date-navigation mb-3 d-flex justify-content-between">
        <!-- 前日ボタン -->
        <form method="GET" action="{{ route('admin.attendance.list') }}" class="previous-day">
            @csrf
            <button type="submit" name="date" value="{{ \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d') }}" class="btn btn-secondary">
                <img src="/images/arrow.png" alt="左矢印" class="arrow-icon"> 前日
            </button>
        </form>

        <!-- 現在の日付 -->
         <div class="current-date d-flex align-items-center">
        <img src="/images/calendar.png" alt="カレンダー" class="date-icon">
            {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}
        </div>

        <!-- 翌日ボタン -->
        <form method="GET" action="{{ route('admin.attendance.list') }}" class="next-day">
            @csrf
            <button type="submit" name="date" value="{{ \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d') }}" class="btn btn-secondary">
                翌日 <img src="/images/arrow.png" alt="右矢印" class="arrow-icon flipped">
            </button>
        </form>
    </div>

    <!-- 勤怠テーブル -->
    <div class="attendance-table-container">
        <table class="table attendance-table">
            <thead class="attendance-table-header">
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-table-body">
                @foreach ($users as $user)
                    @php
                        // 現在のユーザーの勤怠データを取得
                        $attendance = $attendances->where('user_id', $user->id)->first();

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

                    <!-- 勤怠データが存在する場合のみ行を表示 -->
                    @if ($attendance)
                        <tr>
                            <!-- 名前 -->
                            <td class="attendance-name">{{ $user->name }}</td>

                            <!-- 出勤時間 -->
                            <td class="attendance-time-in">{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '' }}</td>

                            <!-- 退勤時間 -->
                            <td class="attendance-time-out">{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '' }}</td>

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
                                <a href="{{ route('attendance.details', ['date' => $currentDate]) }}?user_id={{ $user->id }}" class="btn btn-primary">詳細</a>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection