<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Address;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            Item::where('id', $item_id)
                ->where('status', 'active')
                ->update(['status' => 'sold']);

            Purchase::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'item_id' => $item_id,
                ],
                [
                    'address_id' => $request->address_id,
                ]
            );

            return redirect('/');
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

            'success_url' => url("/purchase/{$item->id}/success").'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url("/purchase/{$item->id}/cancel"),

            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'pay' => 'card',
            ],
        ]);

        return redirect($session->url);
    }

    public function success($item_id)
    {
        $user = Auth::user();

        Item::where('id', $item_id)
            ->where('status', 'active')
            ->update(['status' => 'sold']);

        Purchase::firstOrCreate([
            'user_id' => $user->id,
            'item_id' => $item_id,
        ]);

        return redirect('/');
    }

    public function cancel($item_id)
    {
        return redirect('/');
    }
}
