@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="create-form__content">
    <div class="create-form__heading">
        <h2>商品の出品</h2>
    </div>

    <form action="/sell" method="post" enctype="multipart/form-data">
        @csrf

        <div class="product-image-title">
            商品画像
        </div>

        <div class="product-image-box">
            <div class="form__error">
                @error('image')
                {{ $message}}
                @enderror
            </div>
        </div>

        <label class="product-image-select">
            画像を選択する
            <input type="file" name="image" accept="image/*" style="display:none;">
        </label>
        

        <div class="product-detail-title">
            商品詳細
        </div>

        <div class="category-title">カテゴリー</div>

        <div class="category-area">
            @php
                $oldCategories = old('categories', []);
            @endphp

            @foreach ($categories as $category)
                <label class="category-btn">
                    <input
                        type="checkbox"
                        name="categories[]"
                        value="{{ $category->id }}"
                        {{ in_array($category->id, $oldCategories) ? 'checked' : '' }}
                    >
                    <span>{{ $category->name }}</span>
                </label>
            @endforeach
            <div class="form__error">
            @error('categories')
            {{ $message }}
            @enderror
        </div>
        </div>
        

        <div class="product-status">
            <div class="product-status__label">
                商品の状態
            </div>

            <select name="condition" class="product-status__select">
                <option value="">選択してください</option>
                <option value="良好" {{ old('condition') === '良好' ? 'selected' : '' }}>良好</option>
                <option value="目立った傷や汚れなし" {{ old('condition') === '目立った傷や汚れなし' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                <option value="やや傷や汚れあり" {{ old('condition') === 'やや傷や汚れあり' ? 'selected' : '' }}>やや傷や汚れあり</option>
                <option value="状態が悪い" {{ old('condition') === '状態が悪い' ? 'selected' : '' }}>状態が悪い</option>
            </select>
            <div class="form__error">
            @error('condition')
            {{ $message }}
            @enderror
            </div>
        </div>
        

        <div class="product-detail__section-title">
            商品名と説明
        </div>

        <div class="product-form">
            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__label--item">商品名</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="name" value="{{ old('name') }}" />
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
                    <span class="form__input--item">ブランド名</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <input type="text" name="brand_name" value="{{ old('brand_name') }}" />
                    </div>
                </div>
            </div>

            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__input--item">商品の説明</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--textarea">
                        <textarea name="description">{{ old('description') }}</textarea>
                    </div>
                    <div class="form__error">
                        @error('description')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form__group">
                <div class="form__group-title">
                    <span class="form__input--item">販売価格</span>
                </div>
                <div class="form__group-content">
                    <div class="form__input--text">
                        <span class="yen-mark">￥</span>
                        <input type="text" name="price" value="{{ old('price') }}" />
                    </div>
                    <div class="form__error">
                        @error('price')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form__button">
                <button class="form__button-submit" type="submit">出品する</button>
            </div>
        </div>
    </form>
</div>
@endsection
