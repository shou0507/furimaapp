<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>furima</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <a href="{{ url('/')}}">
                <img src="{{ asset('/img/COACHTECHヘッダーロゴ.png')}}" class="header__logo">
            </a>
            
            <div class="header-search">
    <form action="{{ url()->current() }}" method="GET" class="header-search__box">
        <input
            type="text"
            name="keyword"
            class="header-search__input"
            placeholder="何かお探しですか？"
            value="{{ request('keyword') }}"
        >
    </form>
</div>

            <nav class="header-nav">
            @auth
            <form action="/logout" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="header-nav__link">ログアウト</button>
        </form>
        <a href="/mypage" class="header-nav__link">マイページ</a>
        <a href="/sell" class="header-nav__sell">出品</a>
            @endauth

        @guest
        <a href="/login" class="header-nav__link">ログイン</a>
        <a href="/mypage" class="header-nav__link">マイページ</a>
        <a href="/sell" class="header-nav__sell">出品</a>
        @endguest

</nav>


        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
