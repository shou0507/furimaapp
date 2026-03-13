<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionMessage;
use App\Models\TransactionMessageRead;
use App\Models\Rating;
use App\Http\Requests\StoreTransactionMessageRequest;
use App\Mail\TransactionCompleteMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{
    public function show(Transaction $transaction)
{
    if ($transaction->buyer_id !== Auth::id() && $transaction->seller_id !== Auth::id()) {
        abort(403);
    }

    $userId = Auth::id();

    $transaction->load(['item', 'buyer', 'seller', 'messages.user']);

    $isBuyer = $transaction->buyer_id === $userId;
    $isSeller = $transaction->seller_id === $userId;
    $partner = $isBuyer ? $transaction->seller : $transaction->buyer;

    $alreadyRated = Rating::where('transaction_id', $transaction->id)
        ->where('from_user_id', $userId)
        ->exists();

    $shouldOpenRatingModal = false;

    // 出品者が取引完了後にチャット画面を開いたとき、
    // まだ未評価なら自動でモーダル表示
    if ($isSeller && $transaction->is_completed && !$alreadyRated) {
        $shouldOpenRatingModal = true;
    }

    // この取引の最新メッセージIDを取得
    $latestMessageId = $transaction->messages()->max('id');

    // この取引を開いたら、そのユーザーの既読位置を最新メッセージまで進める
    if ($latestMessageId) {
        TransactionMessageRead::updateOrCreate(
            [
                'transaction_id' => $transaction->id,
                'user_id' => $userId,
            ],
            [
                'last_read_message_id' => $latestMessageId,
            ]
        );
    }

    $otherTransactions = Transaction::query()
        ->where(function ($query) use ($userId) {
            $query->where('buyer_id', $userId)
                ->orWhere('seller_id', $userId);
        })
        ->where('id', '!=', $transaction->id)
        ->with(['item'])
        ->get()
        ->map(function ($otherTransaction) use ($userId) {
            $read = TransactionMessageRead::where('transaction_id', $otherTransaction->id)
                ->where('user_id', $userId)
                ->first();

            $lastReadMessageId = optional($read)->last_read_message_id ?? 0;

            $unreadCount = TransactionMessage::where('transaction_id', $otherTransaction->id)
                ->where('sender_id', '!=', $userId)
                ->where('id', '>', $lastReadMessageId)
                ->count();

            $latestMessageId = TransactionMessage::where('transaction_id', $otherTransaction->id)
                ->max('id');

            $otherTransaction->unread_count = $unreadCount;
            $otherTransaction->latest_message_id = $latestMessageId ?? 0;

            return $otherTransaction;
        })
        ->sortByDesc('latest_message_id')
        ->values();

    return view('transactions.show', compact(
        'transaction',
        'isBuyer',
        'isSeller',
        'partner',
        'otherTransactions',
        'shouldOpenRatingModal'
    ));
}

    public function complete(Transaction $transaction)
    {
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($transaction->is_completed) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'この取引は完了しています。');
        }

        $transaction->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $transaction->load(['item', 'buyer', 'seller']);

        Mail::to($transaction->seller->email)
            ->send(new TransactionCompleteMail($transaction));

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', '取引を完了し、出品者へ通知メールを送信しました。');
    }

    public function storeMessage(StoreTransactionMessageRequest $request, Transaction $transaction)
    {
        if ($transaction->buyer_id !== Auth::id() && $transaction->seller_id !== Auth::id()) {
            abort(403);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('transaction_messages', 'public');
        }

        TransactionMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('transactions.show', $transaction);
    }

    public function updateMessage(Request $request, Transaction $transaction, TransactionMessage $message)
    {
        if ($transaction->buyer_id !== Auth::id() && $transaction->seller_id !== Auth::id()) {
            abort(403);
        }

        if ($message->transaction_id !== $transaction->id) {
            abort(404);
        }

        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        $message->update([
            'message' => $request->message,
        ]);

        return redirect()->route('transactions.show', $transaction);
    }

    public function destroyMessage(Transaction $transaction, TransactionMessage $message)
    {
        if ($transaction->buyer_id !== Auth::id() && $transaction->seller_id !== Auth::id()) {
            abort(403);
        }

        if ($message->transaction_id !== $transaction->id) {
            abort(404);
        }

        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        $message->delete();

        return redirect()->route('transactions.show', $transaction);
    }
}