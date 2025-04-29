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
                            <input type="text" name="time_in" 
                                   value="{{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '' }}" 
                                   class="form-control me-2">
                            〜
                            <input type="text" name="time_out" 
                                   value="{{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '' }}" 
                                   class="form-control ms-2">
                        </div>
                        <!-- 出勤・退勤時間のエラーメッセージ -->
                        @error('time_in')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @error('time_out')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td>
                        <!-- 既存の休憩データをループで表示 -->
                        <div id="break-fields">
                            @if ($breakTimes && $breakTimes->count() > 0)
                                @foreach ($breakTimes as $index => $break)
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2">休憩{{ $index + 1 }}</span>
                                        <input type="text" name="breaks[{{ $index }}][break_in]" 
                                               value="{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '' }}" 
                                               class="form-control me-2" >
                                        〜
                                        <input type="text" name="breaks[{{ $index }}][break_out]" 
                                               value="{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}" 
                                               class="form-control ms-2" >
                                    </div>
                                    <!-- 休憩時間のエラーメッセージ -->
                                    @error("breaks.$index.break_in")
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    @error("breaks.$index.break_out")
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            @endif
                        </div>

                        <!-- 新しい休憩フィールド -->
                        <div class="d-flex align-items-center mt-2">
                            <span class="me-2">休憩{{ $index + 2 }}</span>
                            <input type="text" name="breaks[new][break_in]" 
                                   value="" class="form-control me-2" >
                            〜
                            <input type="text" name="breaks[new][break_out]" 
                                   value="" class="form-control ms-2" >
                        </div>
                        <!-- 新しい休憩時間のエラーメッセージ -->
                        @error('breaks.new.break_in')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @error('breaks.new.break_out')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" class="form-control" rows="3">{{ $attendance->remarks ?? '' }}</textarea>
                        <!-- 備考欄のエラーメッセージ -->
                        @error('remarks')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
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