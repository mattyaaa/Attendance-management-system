@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request_list.css') }}">
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
        <!-- 承認待ちタブ -->
        @if ($currentTab === 'pending')
            @if ($pendingRequests->isEmpty())
                <div class="alert alert-info">承認待ちの修正申請はありません。</div>
            @else
                <table class="request-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">状態</th>
                            <th style="width: 15%;">名前</th>
                            <th style="width: 20%;">対象日時</th>
                            <th style="width: 20%;">申請理由</th>
                            <th style="width: 20%;">申請日時</th>
                            <th style="width: 20%;">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingRequests as $request)
                        <tr>
                            <td>承認待ち</td>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->date)->format('Y/m/d') }}</td>
                            <td>{{ $request->remarks }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('admin.request.approve', ['attendance_correct_request' => $request->id]) }}" class="btn btn-primary">詳細</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        <!-- 承認済みタブ -->
        @elseif ($currentTab === 'approved')
            @if ($approvedRequests->isEmpty())
                <div class="alert alert-info">承認済みの修正申請はありません。</div>
            @else
                <table class="request-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">状態</th>
                            <th style="width: 15%;">名前</th>
                            <th style="width: 20%;">対象日時</th>
                            <th style="width: 20%;">申請理由</th>
                            <th style="width: 20%;">申請日時</th>
                            <th style="width: 20%;">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvedRequests as $request)
                        <tr>
                            <td>承認済み</td>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->date)->format('Y/m/d') }}</td>
                            <td>{{ $request->remarks }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('admin.request.approve', ['attendance_correct_request' => $request->id]) }}" class="btn btn-primary">詳細</a>
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