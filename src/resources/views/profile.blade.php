@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="user-info">
    <div class="user-info__avatar">
        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '/images/default.png' }}" alt="">
    </div>

    <div class="user-info__name">
        {{ $user->name ?? 'ユーザー名' }}
    </div>

    <a class="user-info__edit-button" href='/mypage/profile'>
        プロフィールを編集
    </a>
</div>

<div class="top-page">
    <div class="top-page-tabs">
        <a href="/mypage?page=sell" class="tab-btn {{ request('page', 'sell') === 'sell' ? 'active' : '' }}">
            出品した商品
        </a>
        <a href="/mypage?page=buy" class="tab-btn {{ request('page') === 'buy' ? 'active' : '' }}">
            購入した商品
        </a>
    </div>
</div>
<div class="tab-underline"></div>

<div class="tab-content active">
    <div class="items-grid">
    @foreach ($items as $item)
        <div class="item-card">
            <a href="/item/{{ $item->id }}" class="item-card__link">
                <div class="item-card__image-wrap">
                    @if ($item->status === 'sold')
                        <span class="item-card__sold-badge">Sold</span>
                    @endif

                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="product-image">
                </div>

                <div class="item-card__info">
                    <p class="item-card__title">{{ $item->name }}</p>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
