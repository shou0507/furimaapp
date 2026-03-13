<?php

namespace App\Http\Controllers;

use App\Mail\TransactionCompleteMail;
use App\Models\Rating;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RatingController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ], [
            'rating.required' => '評価を選択してください',
        ]);

        $user = auth()->user();

        $isBuyer = $user->id === $transaction->buyer_id;
        $isSeller = $user->id === $transaction->seller_id;

        if (!$isBuyer && !$isSeller) {
            abort(403);
        }

        $alreadyRated = Rating::where('transaction_id', $transaction->id)
            ->where('from_user_id', $user->id)
            ->exists();

        if ($alreadyRated) {
            return redirect()->route('items.index');
        }

        // 出品者は購入者が完了してから評価可能
        if ($isSeller && !$transaction->is_completed) {
            abort(403);
        }

        // 購入者は評価送信時に取引完了にする
        if ($isBuyer && !$transaction->is_completed) {
            $transaction->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            $transaction->load(['item', 'buyer', 'seller']);

            Mail::to($transaction->seller->email)
                ->send(new TransactionCompleteMail($transaction));
        }

        $toUserId = $isBuyer
            ? $transaction->seller_id
            : $transaction->buyer_id;

        Rating::create([
            'transaction_id' => $transaction->id,
            'from_user_id' => $user->id,
            'to_user_id' => $toUserId,
            'score' => $request->rating,
        ]);

        return redirect()->route('items.index');
    }
}