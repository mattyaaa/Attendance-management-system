@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">勤怠詳細</h1>
    
    <!-- ユーザーの名前と日付を表示 -->
    <h3>名前: {{ Auth::user()->name }}</h3>
    <h3>日付: {{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}</h3>

    @if ($attendance)
        <form method="POST" action="{{ route('attendance.update', ['date' => $date]) }}">
            @csrf
            @method('PUT')

            <!-- 勤怠情報を表示 -->
            <table class="table table-bordered">
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="d-flex align-items-center">
                            <input type="time" name="time_in" value="{{ $attendance->time_in }}" class="form-control me-2">
                            〜
                            <input type="time" name="time_out" value="{{ $attendance->time_out }}" class="form-control ms-2">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="d-flex align-items-center">
                            <input type="time" name="break_in_1" value="{{ $breakTime->break_in_1 ?? '' }}" class="form-control me-2">
                            〜
                            <input type="time" name="break_out_1" value="{{ $breakTime->break_out_1 ?? '' }}" class="form-control ms-2">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" class="form-control" rows="3">{{ $attendance->remarks ?? '' }}</textarea>
                    </td>
                </tr>
            </table>

            <!-- 修正ボタン -->
            <button type="submit" class="btn btn-primary mt-3">修正</button>
        </form>
    @else
        <p>この日の勤怠情報はありません。</p>
    @endif

</div>
@endsection