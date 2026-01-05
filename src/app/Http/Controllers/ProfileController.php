<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
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
        }

        return view('profile', compact('user', 'page', 'items'));
    }
}
