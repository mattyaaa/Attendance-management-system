<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者ログイン</title>
</head>
<body>
    <h1>管理者 ログイン</h1>
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label for="email">メールアドレス:</label>
        <input type="email" name="email" id="email" required>
        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">ログイン</button>
    </form>
</body>
</html>