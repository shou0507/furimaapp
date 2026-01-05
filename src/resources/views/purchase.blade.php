@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}?v={{ time() }}">
@endsection

@section('content')
<form action="/purchase/{{ $item->id }}/checkout" method="post">
    @csrf
    <input type="hidden" name="address_id" value="{{ optional($address)->id }}">
<div class="purchase-item">
    <div class="purchase-item__thumb">
        <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
    </div>

    <div class="purchase-item__info">
        <div class="purchase-item__name">{{ $item->name }}</div>
        <div class="purchase-item__price">
            <span class="purchase-item__yen">¥</span>
            <span class="purchase-item__amount">{{ number_format($item->price) }}</span>
        </div>
    </div>

    <div class="purchase-line"></div>

    <div class="payment-method">
        <div class="payment-method__label">支払い方法</div>

        <div class="payment-method__select">
            <select name="payment_method" id="payment_method">
                <option value="" hidden>選択してください</option>
                <option value="konbini">コンビニ支払い</option>
                <option value="card">カード支払い</option>
            </select>
            <div class="payment-method__icon"></div>
        </div>
        @error('payment_method')
        <div class="form__error">{{ $message }}</div>
        @enderror
    </div>
    <div class="purchase-line2"></div>
    <div class="shipping-section">
        <div class="shipping-title">配送先</div>
        <a href="/purchase/address/{{ $item->id }}" class="shipping-edit">
            変更する
        </a>
        <div class="shipping-address">
            〒 {{ $address->postal_code }}<br>
                {{ $address->address }}
                @if(($address)->building)
                    <br>{{ $address->building }}
                @endif
        </div>
        @error('address_id')
        <div class="form__error">{{ $message }}</div>
        @enderror

        <div class="purchase-line3"></div>
    </div>
    <div class="confirm-surface">
        <div class="confirm-row">
            <div class="confirm-label">商品代金</div>
            <div class="confirm-value">¥ {{ number_format($item->price) }}</div>
        </div>

        <div class="confirm-row">
            <div class="confirm-label">支払い方法</div>
            <div class="confirm-value" id="summary_payment_method">
                    選択してください
            </div>
        </div>
    </div>
    <button class="purchase-button">
    購入する
    </button>
</div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('payment_method');
        const summary = document.getElementById('summary_payment_method');

        select.addEventListener('change', function () {
            if (this.value === 'konbini') {
                summary.textContent = 'コンビニ支払い';
            } else if (this.value === 'card') {
                summary.textContent = 'カード支払い';
            } else {
                summary.textContent = '選択してください';
            }
        });
    });
</script>

@endsection