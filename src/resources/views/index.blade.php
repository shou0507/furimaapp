@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="top-page">
    <div class="top-page-tabs">
        <a href="/" class="tab-btn {{ request('tab') === 'mylist' ? '' : 'active' }}">
            おすすめ
        </a>
        <a href="/?tab=mylist" class="tab-btn {{ request('tab') === 'mylist' ? 'active' : '' }}">
            マイリスト
        </a>
    </div>
</div>

@if (request('tab') !== 'mylist')
    <div class="tab-content active" id="recommend">
        <div class="items-grid">
            @foreach ($items as $item)
                <div class="item-card">
                    <a href="/item/{{ $item->id }}" class="item-card__link">
                        <div class="item-card__image-wrap">
                            @if ($item->status === 'sold')
                                <span class="item-card__sold-badge">Sold</span>
                            @endif

                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="item-card__image">
                        </div>

                        <div class="item-card__info">
                            <p class="item-card__title">{{ $item->name }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if (request('tab') === 'mylist')
    <div class="tab-content active" id="mylist">
        @auth
            <div class="items-grid">
                @foreach ($mylistItems as $item)
                    <div class="item-card">
                        <a href="/item/{{ $item->id }}" class="item-card__link">
                            <div class="item-card__image-wrap">
                                @if ($item->status === 'sold')
                                    <span class="item-card__sold-badge">Sold</span>
                                @endif

                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="item-card__image">
                            </div>

                            <div class="item-card__info">
                                <p class="item-card__title">{{ $item->name }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endauth
    </div>
@endif
@endsection
