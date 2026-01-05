@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/show.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="product-image-area">
    <div class="product-image-box">
        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="product-image">
    </div>
</div>

<div class="product-description-area">

    <div class="product-title">
        <div class="product-name">{{ $item->name }}</div>
        <div class="product-brand">
            {{ $item->brand_name ?: 'ブランドなし' }}
        </div>
        <div class="product-price">
            ¥{{ number_format($item->price) }} (税込)
        </div>
    </div>

    <div class="product-actions">
        <div class="favorite-block">
            <a href="/favorite/toggle/{{ $item->id }}">
                @if($isFavorited)
                    <img src="/img/ハートロゴ_ピンク.png" class="action-icon heart">
                @else
                    <img src="/img/ハートロゴ_デフォルト.png" class="action-icon heart">
                @endif
            </a>
            <span class="action-count">{{ $favoriteCount }}</span>
        </div>

        <div class="comment-block">
            <img src="/img/ふきだしロゴ.png" class="action-icon comment">
            <span class="action-count">{{ $commentCount }}</span>
        </div>
    </div>


    <div class="form__button">
        <button class="form__button-submit" type="button" onclick="location.href='/purchase/{{ $item->id }}'">購入手続きはこちら</button>
    </div>

    <div class="product-description">
        <div class="product-description__title">商品説明</div>

        <div class="product-description__text">
            {{ $item->description }}
        </div>
    </div>

    <div class="product-info">
        <div class="product-info__title">商品の情報</div>

        <div class="product-info__row">
            <div class="product-info__label">カテゴリー</div>
            <div class="product-info__value">
                @foreach ($item->categories as $category)
                    <span class="category-pill">{{ $category->name }}</span>
                @endforeach
            </div>
        </div>

        <div class="product-info__row">
            <div class="product-info__label">商品の状態</div>
            <div class="product-info__value">
                <span class="product-condition-text">{{ $item->condition }}</span>
            </div>
        </div>
    </div>

    <div class="product-comments">
    <div class="product-comments__title">
        コメント({{ $commentCount }})
    </div>

    <div class="comments-list">

        @foreach ($item->comments as $comment)
            <div class="comment">
                <div class="comment__avatar">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '/images/default.png' }}" alt="">
                </div>

                <div class="comment__body">
                    <div class="comment__author">
                        {{ optional($comment->user)->name ?? '退会ユーザー' }}
                    </div>
                </div>
            </div>

            <div class="comment-box">
                {{ $comment->comment }}
            </div>
            @endforeach
        </div>
    </div>

    <form action="/item/{{ $item->id }}/comment" method="post" class="comment-form">
        @csrf
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__input--item">商品へのコメント</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--textarea">
                    <textarea name="comment">{{ old('comment') }}</textarea>
                </div>
                @error('comment')
                <div class="error-message">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
        <div class="comments-button">
            <button type="submit" class="comments-button__inner">
                コメントする
            </button>
        </div>
    </form>
</div>
@endsection
