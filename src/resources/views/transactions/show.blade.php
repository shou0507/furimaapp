@extends('layouts.guest')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/transaction.css') }}?v={{ time() }}">
@endsection

@section('content')
    <div class="transaction-page">

        <aside class="sidebar">
            <div class="sidebar-title">
                その他の取引
            </div>

            @foreach ($otherTransactions ?? collect() as $otherTransaction)
                <a href="{{ route('transactions.show', $otherTransaction) }}" class="sidebar-transaction">
                    <div class="sidebar-item-name">
                        {{ $otherTransaction->item->name }}
                    </div>

                    @if (($otherTransaction->unread_count ?? 0) > 0)
                        <span class="sidebar-notification-badge">
                            {{ $otherTransaction->unread_count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </aside>

        <main class="main-content">
            <div class="top-header">
                <div class="header-left">
                    <div class="user-avatar-large"></div>

                    <h1 class="transaction-title">
                        「{{ $partner->name }}」さんとの取引画面
                    </h1>
                </div>

                @if ($isBuyer && !$transaction->is_completed)
                    <button type="button" class="complete-btn" onclick="openRatingModal()">
                        取引を完了する
                    </button>
                @endif
            </div>

            <hr class="divider">

            <section class="product-section">
                <div class="product-image">
                    <img src="{{ $transaction->item->image_url }}" alt="{{ $transaction->item->name }}">
                </div>

                <div class="product-info">
                    <div class="product-name">
                        {{ $transaction->item->name }}
                    </div>

                    <div class="product-price">
                        ¥{{ number_format($transaction->item->price) }}
                    </div>
                </div>
            </section>

            <hr class="divider">

            <section class="chat-area">
                @foreach ($transaction->messages as $message)
                    @if ($message->sender_id === auth()->id())
                        <div class="chat-row my-chat">
                            <div class="chat-user-right">
                                <div class="chat-username">
                                    {{ $message->user->name }}
                                </div>
                                <div class="avatar-small"></div>
                            </div>

                            @if ($message->message)
                                <div class="message-right image-message">
                                    <span id="message-text-{{ $message->id }}">
                                        {{ $message->message }}
                                    </span>

                                    <form id="edit-form-{{ $message->id }}"
                                        action="{{ route('transactions.messages.update', [$transaction, $message]) }}"
                                        method="POST" style="display: none;">
                                        @csrf
                                        @method('PUT')

                                        <input type="text" name="message" value="{{ $message->message }}"
                                            class="edit-input" onkeydown="submitOnEnter(event, this)">
                                    </form>
                                </div>
                            @endif

                            @if ($message->image_path)
                                <div class="message-right image-message">
                                    <img class="chat-image" src="{{ asset('storage/' . $message->image_path) }}" alt="メッセージ画像">
                                </div>
                            @endif

                            <div class="message-actions">
                                <button class="action-btn" type="button" onclick="editMessage({{ $message->id }})">
                                    編集
                                </button>

                                <form action="{{ route('transactions.messages.destroy', [$transaction, $message]) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="action-btn">
                                        削除
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="chat-row partner-chat">
                            <div class="chat-user-left">
                                <div class="avatar-small"></div>

                                <div class="chat-username">
                                    {{ $message->user->name }}
                                </div>
                            </div>

                            @if ($message->message)
                                <div class="message-left">
                                    {{ $message->message }}
                                </div>
                            @endif

                            @if ($message->image_path)
                                <div class="message-left">
                                    <img class="chat-image" src="{{ asset('storage/' . $message->image_path) }}" alt="メッセージ画像">
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </section>
        </main>
    </div>

    <div class="message-form-area">
        <form class="message-form" action="{{ route('transactions.messages.store', $transaction) }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <div class="message-input-area">
                @error('message')
                    <div class="message-error">{{ $message }}</div>
                @enderror

                @error('image')
                    <div class="message-error">{{ $message }}</div>
                @enderror

                <textarea name="message" id="messageInput" class="message-input" placeholder="取引メッセージを記入してください">{{ old('message') }}</textarea>
            </div>

            <input type="file" name="image" id="image" hidden>

            <label for="image" class="image-add-btn">
                画像を追加
            </label>

            <button type="submit" class="send-btn">
                <img src="{{ asset('/img/e99395e98ea663a8400f40e836a71b8c4e773b01.jpg') }}" alt="送信">
            </button>
        </form>
    </div>

    {{-- 評価モーダル --}}
    <div id="ratingModal" class="rating-modal-overlay">
        <div class="rating-modal">
            <div class="rating-modal-header">
                <h2>取引が完了しました。</h2>
            </div>

            <div class="rating-modal-body">
                <p class="rating-modal-text">今回の取引相手はどうでしたか？</p>

                <form action="{{ route('transactions.rating.store', $transaction) }}" method="POST" id="ratingForm">
                    @csrf

                    <input type="hidden" name="rating" id="ratingValue" value="">

                    <div class="rating-stars">
                        <button type="button" class="star-btn" data-value="1">★</button>
                        <button type="button" class="star-btn" data-value="2">★</button>
                        <button type="button" class="star-btn" data-value="3">★</button>
                        <button type="button" class="star-btn" data-value="4">★</button>
                        <button type="button" class="star-btn" data-value="5">★</button>
                    </div>

                    <div class="rating-modal-footer">
                        <button type="submit" class="rating-submit-btn">送信する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editMessage(id) {
            const text = document.getElementById('message-text-' + id);
            const form = document.getElementById('edit-form-' + id);

            if (text) {
                text.style.display = 'none';
            }

            if (form) {
                form.style.display = 'block';

                const input = form.querySelector('input[name="message"]');
                if (input) {
                    input.focus();
                    input.setSelectionRange(input.value.length, input.value.length);
                }
            }
        }

        function submitOnEnter(event, input) {
            if (event.key === 'Enter') {
                event.preventDefault();
                input.closest('form').submit();
            }

            if (event.key === 'Escape') {
                window.location.reload();
            }
        }

        function openRatingModal() {
            const modal = document.getElementById('ratingModal');
            modal.classList.add('is-open');
        }

        function closeRatingModal() {
            const modal = document.getElementById('ratingModal');
            modal.classList.remove('is-open');
        }

        const starButtons = document.querySelectorAll('.star-btn');
        const ratingValue = document.getElementById('ratingValue');

        starButtons.forEach((button) => {
            button.addEventListener('click', function() {
                const value = Number(this.dataset.value);
                ratingValue.value = value;

                starButtons.forEach((star, index) => {
                    if (index < value) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            });
        });

        const ratingForm = document.getElementById('ratingForm');
        if (ratingForm) {
            ratingForm.addEventListener('submit', function(e) {
                if (!ratingValue.value) {
                    e.preventDefault();
                    alert('評価を選択してください');
                    return;
                }

                closeRatingModal();
            });
        }

        document.getElementById('ratingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRatingModal();
            }
        });

        @if (!empty($shouldOpenRatingModal) && $shouldOpenRatingModal)
            window.addEventListener('load', function() {
                openRatingModal();
            });
        @endif

        const transactionId = @json($transaction->id);
        const storageKey = `transaction_${transactionId}_message`;
        const messageInput = document.getElementById('messageInput');
        const messageForm = document.querySelector('.message-form');

        if (messageInput) {
            const savedMessage = localStorage.getItem(storageKey);

            if (!messageInput.value && savedMessage !== null) {
                messageInput.value = savedMessage;
            }

            messageInput.addEventListener('input', function() {
                localStorage.setItem(storageKey, messageInput.value);
            });
        }

        if (messageForm) {
            messageForm.addEventListener('submit', function() {
                localStorage.removeItem(storageKey);
            });
        }
    </script>
@endsection
