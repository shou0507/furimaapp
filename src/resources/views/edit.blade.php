@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit.css') }}?v={{ time() }}">
@endsection

@section('content')
@php
    $user = Auth::user();
@endphp
<div class="edit-form__content">
    <div class="edit-form__heading">
        <h2>プロフィール設定</h2>
    </div>
    <form action="/mypage/profile" class="form" method="post" enctype="multipart/form-data">
        @csrf
        <div class="profile">
            <div class="profile__image">
                <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '/images/default.png' }}" alt="">
            </div>

            <input id="avatar" type="file" name="avatar" accept="image/*" hidden>

            <button type=button class="image-select-button" onclick="document.getElementById('avatar').click()" >画像を選択する</button>

            <div class="form__error">
                @error('avatar')
                {{ $message }}
                @enderror
            </div>
        </div>


            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__label--item">ユーザー名</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" />
                    </div>
                    <div class="form__error">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__label--item">郵便番号</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="postal_code" value="{{ old('postal_code', optional($address)->postal_code) }}" />
                    </div>
                    <div class="form__error">
                        @error('postal_code')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__input--item">住所</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="address" value="{{ old('address', optional($address)->address) }}" />
                    </div>
                    <div class="form__error">
                        @error('address')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__input--item">建物名</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="building" value="{{ old('building', optional($address)->building) }}">
                    </div>
                </div>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">更新する</button>
            </div>
    </form>
</div>