@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register__content">
  <div class="register-form__heading">
    <h2>会員登録</h2>
  </div>
  <form class="form" action="{{ route('register') }}" method="post">
    @csrf
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">名前</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="text" name="username" value="{{ old('username') }}" />
        </div>
        <div class="form__error">
          @error('username')
          <span class="error-message">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">メールアドレス</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="email" name="email" value="{{ old('email') }}" />
        </div>
        <div class="form__error">
          @error('email')
          <span class="error-message">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">パスワード</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="password" name="password" />
        </div>
        <div class="form__error">
          @error('password')
          <span class="error-message">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>
    <div class="form__group">
      <div class="form__group-title">
        <span class="form__label--item">パスワード確認</span>
      </div>
      <div class="form__group-content">
        <div class="form__input--text">
          <input type="password" name="password_confirmation" />
        </div>
        <div class="form__error">
          @error('password_confirmation')
          <span class="error-message">{{ $message }}</span>
          @enderror
        </div>
      </div>
    </div>
    <div class="form__button">
      <button class="form__button-submit" type="submit">登録する</button>
    </div>
  </form>
  <div class="login__link">
    <a class="login__button-submit" href="{{ route('login') }}">ログインはこちら</a>
  </div>
</div>
@endsection