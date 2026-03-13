<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function sellingItems(Request $request)
    {
        $user = Auth::user();

        $page = $request->query('page', 'sell');

        if ($page === 'sell') {
            $items = Item::where('user_id', $user->id)->get();

        } elseif ($page === 'buy') {
            $items = Purchase::where('user_id', $user->id)
                ->with('item')
                ->get()
                ->pluck('item');
        } elseif ($page === 'trading') {
            $items = Transaction::with('item')
                ->where('status', 'trading')
                ->where(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                })
                ->orderByDesc('last_message_at')
                ->get();
        } else {
            $items = collect();
        }

        return view('profile', compact('user', 'page', 'items'));
    }
}
