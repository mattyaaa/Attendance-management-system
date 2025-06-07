@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request_approval.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="mb-4">勤怠詳細</h1>
    <form id="approve-form" method="POST" action="{{ route('admin.request.approve_action', ['attendance_correct_request' => $request->id]) }}">
        @csrf
        @method('PATCH')
        <table class="table table-bordered">
            <tr>
                <th>名前</th>
                <td>{{ $request->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span class="year">{{ \Carbon\Carbon::parse($request->date)->format('Y年') }}</span>
                    <span class="month-day">{{ \Carbon\Carbon::parse($request->date)->format('m月d日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="text" readonly class="form-control me-2"
                        value="{{ $request->time_in ? \Carbon\Carbon::parse($request->time_in)->format('H:i') : '' }}">
                    〜
                    <input type="text" readonly class="form-control ms-2"
                        value="{{ $request->time_out ? \Carbon\Carbon::parse($request->time_out)->format('H:i') : '' }}">
                </td>
            </tr>
            @php $breakIndex = 1; @endphp
            @if($request->breaks && $request->breaks->count())
                @foreach($request->breaks as $break)
                <tr>
                    <th>休憩{{ $breakIndex++ }}</th>
                    <td>
                        <input type="text" readonly class="form-control me-2"
                            value="{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '' }}">
                        〜
                        <input type="text" readonly class="form-control ms-2"
                            value="{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}">
                    </td>
                </tr>
                @endforeach
            @endif
            <tr>
                <th>備考</th>
                <td>
                    <textarea readonly class="form-control" rows="3">{{ $request->remarks }}</textarea>
                </td>
            </tr>
        </table>
        @if($request->status === 'pending')
        <div class="text-right mt-4">
            <button id="approve-btn" type="submit" name="action" value="approve" class="btn btn-success">承認</button>
        @else
        <div class="text-right mt-4">
            <div class="approved-label">
                承認済み
            </div>
        </div>
        @endif
    </form>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('approve-form');
    const approveBtn = document.getElementById('approve-btn');
    const approvedLabel = document.getElementById('approved-label');
    const errorDiv = document.getElementById('ajax-error');

    if (form && approveBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            errorDiv.style.display = 'none';

            if(!confirm('この申請を承認しますか？')) {
                return;
            }

            fetch(form.action, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'approve' })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'approved') {
                    approveBtn.style.display = 'none';
                    approvedLabel.style.display = 'block';
                } else {
                    errorDiv.style.display = 'block';
                    errorDiv.textContent = '承認に失敗しました。';
                }
            })
            .catch(error => {
                errorDiv.style.display = 'block';
                errorDiv.textContent = 'サーバーエラーが発生しました。';
            });
        });
    }
});
</script>
@endsection