<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Address;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function purchase($item_id)
    {
        $item = Item::findOrFail($item_id);

        $user = Auth::user();
        $address = $user->address;

        return view('purchase', compact('item', 'address'));
    }

    public function address(Request $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = $request->user();
        $address = $user->address;

        return view('address', compact('item', 'user', 'address'));
    }

    public function updateAddress(AddressRequest $request, $item_id)
    {
        $address = Address::firstOrNew([
            'user_id' => Auth::id(),
        ]);

        $address->postal_code = $request->postal_code;
        $address->address = $request->address;
        $address->building = $request->building;
        $address->save();

        return redirect("/purchase/$item_id");
    }

    public function checkout(PurchaseRequest $request, $item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        if ($request->payment_method === 'konbini') {
            DB::transaction(function () use ($user, $item, $item_id, $request) {
                $updated = Item::where('id', $item_id)
                    ->where('status', 'active')
                    ->update(['status' => 'sold']);

                if ($updated === 0) {
                    abort(400, 'この商品は購入できません。');
                }

                Purchase::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'item_id' => $item_id,
                    ],
                    [
                        'address_id' => $request->address_id,
                    ]
                );

                Transaction::firstOrCreate(
                    [
                        'item_id' => $item->id,
                        'buyer_id' => $user->id,
                        'seller_id' => $item->user_id,
                    ],
                    [
                        'status' => 'trading',
                        'last_message_at' => now(),
                    ]
                );
            });

            return redirect()->route('mypage.index');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => (int) $item->price,
                    'product_data' => [
                        'name' => $item->name,
                    ],
                ],
            ]],
            'success_url' => url("/purchase/{$item->id}/success") . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url("/purchase/{$item->id}/cancel"),
            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'pay' => 'card',
            ],
        ]);

        return redirect($session->url);
    }

    public function success(Request $request, $item_id)
    {
        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        if (!$request->filled('session_id')) {
            return redirect("/purchase/$item_id")
                ->with('error', '決済セッションが取得できませんでした。');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $session = \Stripe\Checkout\Session::retrieve($request->session_id);

        if ($session->payment_status !== 'paid') {
            return redirect("/purchase/$item_id")
                ->with('error', '決済が完了していません。');
        }

        if (
            (int) $session->metadata->user_id !== $user->id ||
            (int) $session->metadata->item_id !== (int) $item->id
        ) {
            abort(403);
        }

        DB::transaction(function () use ($user, $item, $item_id, $session) {
            $updated = Item::where('id', $item_id)
                ->where('status', 'active')
                ->update(['status' => 'sold']);

            if ($updated === 0) {
                $purchaseExists = Purchase::where('user_id', $user->id)
                    ->where('item_id', $item_id)
                    ->exists();

                if (!$purchaseExists) {
                    abort(400, 'この商品は購入できません。');
                }
            }

            Purchase::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'item_id' => $item_id,
                ],
                [
                    'address_id' => $session->metadata->address_id,
                ]
            );

            Transaction::firstOrCreate(
                [
                    'item_id' => $item->id,
                    'buyer_id' => $user->id,
                    'seller_id' => $item->user_id,
                ],
                [
                    'status' => 'trading',
                    'last_message_at' => now(),
                ]
            );
        });

        return redirect()->route('mypage.index');
    }

    public function cancel($item_id)
    {
        return redirect('/');
    }
}