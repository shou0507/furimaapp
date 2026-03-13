<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Item;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\TransactionMessage;
use App\Models\TransactionMessageRead;
use Illuminate\Http\Request;

class MyPageController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();
    $userId = $user->id;
    $page = $request->query('page', 'sell');

    if ($page === 'sell') {
        $items = Item::where('user_id', $userId)->get();
    } elseif ($page === 'buy') {
        $items = Item::whereHas('transactions', function ($query) use ($userId) {
            $query->where('buyer_id', $userId);
        })->get();
    } elseif ($page === 'trading') {
        $items = Transaction::query()
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
            })
            ->with('item')
            ->get()
            ->map(function ($transaction) use ($userId) {
                $read = TransactionMessageRead::where('transaction_id', $transaction->id)
                    ->where('user_id', $userId)
                    ->first();

                $lastReadMessageId = optional($read)->last_read_message_id ?? 0;

                $transaction->unread_count = TransactionMessage::where('transaction_id', $transaction->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('id', '>', $lastReadMessageId)
                    ->count();

                $transaction->latest_message_id = TransactionMessage::where('transaction_id', $transaction->id)
                    ->max('id') ?? 0;

                return $transaction;
            })
            ->sortByDesc('latest_message_id')
            ->values();
    } else {
        $items = collect();
    }

    $tradingUnreadCount = Transaction::query()
        ->where(function ($query) use ($userId) {
            $query->where('buyer_id', $userId)
                ->orWhere('seller_id', $userId);
        })
        ->get()
        ->sum(function ($transaction) use ($userId) {
            $read = TransactionMessageRead::where('transaction_id', $transaction->id)
                ->where('user_id', $userId)
                ->first();

            $lastReadMessageId = optional($read)->last_read_message_id ?? 0;

            return TransactionMessage::where('transaction_id', $transaction->id)
                ->where('sender_id', '!=', $userId)
                ->where('id', '>', $lastReadMessageId)
                ->count();
        });

    $rating = Rating::where('to_user_id', $userId)->avg('score');
    $rating = is_null($rating) ? null : round($rating);

    return view('profile', compact(
        'user',
        'page',
        'items',
        'tradingUnreadCount',
        'rating'
    ));
}

    public function edit(Request $request)
    {
        $user = $request->user();
        $address = $user->address;

        return view('edit', compact('user', 'address'));
    }

    public function update(ProfileRequest $request)
    {
        $user = $request->user();

        $avatarPath = $user->avatar;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $request->name,
            'avatar' => $avatarPath,
        ]);

        $user->address()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'postal_code' => $request->postal_code,
                'address' => $request->address,
                'building' => $request->building,
            ]
        );

        return redirect('/');
    }
}