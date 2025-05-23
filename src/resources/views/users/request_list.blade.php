@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/users/request_list.css') }}">
@endsection

@section('content')
<div class="request-list-container">
    <!-- タイトル -->
    <div class="request-header">
        <h1 class="request-list-title">申請一覧</h1>
    </div>

    <!-- タブ部分 -->
    <div class="tab-container">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{ $currentTab === 'pending' ? 'active' : '' }}" 
                   href="{{ route('request.list', ['tab' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $currentTab === 'approved' ? 'active' : '' }}" 
                   href="{{ route('request.list', ['tab' => 'approved']) }}">承認済み</a>
            </li>
        </ul>
    </div>

    <!-- データ部分 -->
    <div class="data-container">
        @if ($currentTab === 'pending')
            @if ($pendingRequests->isEmpty())
                <div class="alert alert-info">承認待ちの申請はありません。</div>
            @else
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingRequests as $request)
                        <tr>
                            <td>承認待ち</td>
                            <td>{{ Auth::user()->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->date)->format('Y/m/d') }}</td>
                            <td>{{ $request->remarks }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('attendance.details', ['date' => $request->date]) }}" class="btn-primary">詳細</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($currentTab === 'approved')
            @if ($approvedRequests->isEmpty())
                <div class="alert alert-info">承認済みの申請はありません。</div>
            @else
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvedRequests as $request)
                        <tr>
                            <td>承認済み</td>
                            <td>{{ Auth::user()->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->date)->format('Y/m/d') }}</td>
                            <td>{{ $request->remarks }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('attendance.details', ['date' => $request->date]) }}" class="btn-primary">詳細</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
</div>
@endsection