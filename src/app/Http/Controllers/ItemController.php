<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $itemsQuery = Item::query()
            ->when(Auth::check(), function ($query) {
                $query->where('user_id', '!=', Auth::id());
            });

        if ($keyword) {
            $itemsQuery->where('name', 'LIKE', '%' . $keyword . '%');
        }

        $items = $itemsQuery->get();

        $mylistItems = collect();

        if (auth()->check()) {
            $mylistQuery = auth()->user()->favoriteItems();

            if ($keyword) {
                $mylistQuery->where('name', 'LIKE', '%' . $keyword . '%');
            }

            $mylistItems = $mylistQuery->get();
        }

        return view('index', compact('items', 'mylistItems', 'keyword'));
    }

    public function show(Item $item)
    {
        $user = Auth::user();

        $item->load([
            'favoriteUsers',
            'comments.user',
            'categories',
        ]);

        $favoriteCount = $item->favoriteUsers->count();

        $commentCount = $item->comments->count();

        $isFavorited = false;
        if (Auth::check()) {
            $isFavorited = $item->favorites()
                ->where('user_id', Auth::id())
                ->exists();
        }

        return view('show', compact('item', 'favoriteCount', 'commentCount', 'isFavorited', 'user'));
    }

    public function toggleFavorite(Item $item)
    {
        $user = Auth::user();

        $favorite = \App\Models\Favorite::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            \App\Models\Favorite::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }

        return back();
    }

    public function storeComment(CommentRequest $request, Item $item)
    {
        $userId = Auth::id();

        Comment::create([
            'user_id' => $userId,
            'item_id' => $item->id,
            'comment' => $request->comment,
        ]);

        return back();
    }
}
