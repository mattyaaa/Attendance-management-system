@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/users/attendance_register.css') }}">
@endsection

@section('content')
<div class="container">

    {{-- ステータス表示 --}}
    <div class="status">
        <h2>
            @if ($status === 'working')
                <span class="badge badge-success">出勤中</span>
            @elseif ($status === 'break')
                <span class="badge badge-warning">休憩中</span>
            @elseif ($status === 'finished')
                <span class="badge badge-secondary">退勤済</span>
            @else
                <span class="badge badge-dark">勤務外</span>
            @endif
        </h2>
    </div>

    {{-- 現在の日時表示 --}}
    <?php
    $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
    $currentDay = $weekDays[now()->dayOfWeek];
    ?>
    <div class="current-date">
        <h3 class="current-date__day">{{ now()->format('Y年n月j日') }}（{{ $currentDay }}）</h3>
    <h3 class="current-date__time">{{ now()->format('H:i') }}</h3>

    {{-- 勤務外時 --}}
    @if ($status === 'none')
        <form action="{{ route('attendance.start') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">出勤</button>
        </form>

    {{-- 出勤中時 --}}
    @elseif ($status === 'working')
        <form action="{{ route('attendance.end') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-danger">退勤</button>
        </form>
        <form action="{{ route('attendance.break_start') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-warning">休憩入</button>
        </form>

    {{-- 休憩中時 --}}
    @elseif ($status === 'break')
        <form action="{{ route('attendance.break_end') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">休憩戻</button>
        </form>

    {{-- 退勤後 --}}
    @elseif ($status === 'finished')
        <h3>お疲れ様でした。</h3>
    @endif
</div>
@endsection