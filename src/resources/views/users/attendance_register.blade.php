@extends('layouts.app')

@section('content')
<div class="container">
    <h1>出勤登録</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('attendance.register') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="date">日付</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="time">時間</label>
            <input type="time" id="time" name="time" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">登録</button>
    </form>
</div>
@endsection