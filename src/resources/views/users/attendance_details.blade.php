@extends($layout)

@section('css')
<link rel="stylesheet" href="{{ asset('css/users/attendance_details.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="mb-4">勤怠詳細</h1>

    <form method="POST" action="{{ route('attendance.requestModification', ['attendanceId' => $attendance->id]) }}">
        @csrf
        <table class="table table-bordered">
            <!-- 名前 -->
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>

            <!-- 日付 -->
            <tr>
                <th>日付</th>
                <td>
                    <span class="year">{{ \Carbon\Carbon::parse($date)->format('Y年') }}</span>
                    <span class="space"></span>
                    <span class="month-day">{{ \Carbon\Carbon::parse($date)->format('m月d日') }}</span>
    </td>
            </tr>

            <!-- 出勤・退勤 -->
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <div class="d-flex align-items-center">
                        <!-- 出勤時間 -->
                        <input type="text" name="time_in" 
                               value="{{ old('time_in', $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '') }}" 
                               class="form-control me-2">
                        <span class="range-separator">〜</span>
                        <!-- 退勤時間 -->
                        <input type="text" name="time_out" 
                               value="{{ old('time_out', $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '') }}" 
                               class="form-control ms-2">
                    </div>
                    @error('time_in')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('time_out')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <!-- 休憩 -->
            @php $breakIndex = 1; @endphp
            @if ($breakTimes && $breakTimes->count() > 0)
                @foreach ($breakTimes as $index => $break)
                <tr>
                    <th>休憩{{ $breakIndex++ }}</th>
                    <td>
                        <div class="d-flex align-items-center">
                            <!-- 休憩開始 -->
                            <input type="text" name="breaks[{{ $index }}][break_in]" 
                                   value="{{ old("breaks.$index.break_in", $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '') }}" 
                                   class="form-control me-2">
                            <span class="range-separator">〜</span>
                            <!-- 休憩終了 -->
                            <input type="text" name="breaks[{{ $index }}][break_out]" 
                                   value="{{ old("breaks.$index.break_out", $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '') }}" 
                                   class="form-control ms-2">
                        </div>
                        @error("breaks.$index.break_in")
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @error("breaks.$index.break_out")
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @endforeach
            @endif

            <!-- 新しい休憩 -->
            <tr>
                <th>休憩{{ $breakIndex }}</th>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="text" name="breaks[new][break_in]" 
                               value="" class="form-control me-2">
                        <span class="range-separator">〜</span>
                        <input type="text" name="breaks[new][break_out]" 
                               value="" class="form-control ms-2">
                    </div>
                    @error('breaks.new.break_in')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    @error('breaks.new.break_out')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <!-- 備考 -->
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="remarks" class="form-control" rows="3">{{ $attendance->remarks ?? '' }}</textarea>
                    @error('remarks')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        <!-- 修正ボタン -->
        @if ($isAdmin || $status !== 'pending')
            <div class="text-right mt-4">
                <button type="submit" class="btn btn-primary">修正</button>
            </div>
        @else
            <div class="alert alert-warning">
                ※承認待ちのため修正はできません。
            </div>
        @endif
    </form>
</div>
@endsection