@extends('layouts.guest')

@section('css')
<link rel="stylesheet" href="{{ asset('css/email.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="container">
    <p class="verify-text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <form  method="post" action="/email/verification-notification">
        @csrf
        <button class="verify-button">
            認証はこちらから
        </button>
    </form>

    <form method="post" action="/email/verification-notification">
        @csrf
        <button type="submit" class="resend-link">
            認証メールを再送する
        </button>
    </form>
</div>