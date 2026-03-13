@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ time() }}">
@endsection

@section('content')
    <div class="user-info">
        <div class="user-info__avatar">
            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '/images/default.png' }}" alt="">
        </div>

        <div class="user-info__profile">
            <div class="user-info__name">
                {{ $user->name ?? 'ユーザー名' }}
            </div>

            @if (!is_null($rating))
                <div class="user-info__rating">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $rating)
                            <span class="star active">★</span>
                        @else
                            <span class="star">☆</span>
                        @endif
                    @endfor
                </div>
            @endif
        </div>
        <a href="/mypage/profile" class="user-info__edit-button">
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
            <a href="/mypage?page=trading" class="tab-btn {{ request('page') === 'trading' ? 'active' : '' }}">
                <span>取引中の商品</span>
                @if (($tradingUnreadCount ?? 0) > 0)
                    <span class="tab-btn__badge">
                        {{ $tradingUnreadCount }}
                    </span>
                @endif
            </a>
        </div>
    </div>
    <div class="tab-underline"></div>

    <div class="tab-content active">
        <div class="items-grid">

            {{-- 取引中 --}}
            @if ($page === 'trading')
                @foreach ($items as $transaction)
                    <div class="item-card">

                        <a href="{{ route('transactions.show', $transaction->id) }}" class="item-card__link">
                            <div class="item-card__image-wrap">
                                <img src="{{ $transaction->item->image_url }}" alt="{{ $transaction->item->name }}"
                                    class="product-image">

                                @if (($transaction->unread_count ?? 0) > 0)
                                    <span class="item-card__image-badge">
                                        {{ $transaction->unread_count }}
                                    </span>
                                @endif
                            </div>

                            <div class="item-card__info">
                                <p class="item-card__title">
                                    {{ $transaction->item->name }}
                                </p>
                            </div>
                        </a>
                    </div>
                @endforeach

                {{-- 出品 / 購入 --}}
            @else
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
            @endif
        </div>
    </div>
@endsection
