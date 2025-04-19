<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Management System</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <div class="header-logo">
        <a href="/login"><img src="{{ asset('images/logo.svg') }}" alt="ロゴ"></a>
      </div>
      @if (Auth::check())
      <nav class="header-nav">
        <ul>
          <li><a href="/attendance">勤怠</a></li>
          <li><a href="/attendance/list">勤怠一覧</a></li>
          <li><a href="/stamp_correction_request/list">申請</a></li>
          <li>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
              @csrf
              <button type="submit">ログアウト</button>
            </form>
          </li>
        </ul>
      </nav>
      @endif
    </div>
  </header>

  <main>
    @if (session('status'))
    <div class="alert alert-success">
      {{ session('status') }}
    </div>
    @endif
    @yield('content')
  </main>
</body>

</html>