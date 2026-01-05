<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class SellController extends Controller
{
    public function create()
    {
        $categories = Category::all();

        return view('create', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('item', 'public');
            $path = asset('storage/'.$path);
        }

        $item = Item::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'price' => $request->price,
            'brand_name' => $request->brand_name,
            'description' => $request->description,
            'image_url' => $path,
            'condition' => $request->condition,
            'status' => 'active',
        ]);

        if ($request->has('categories')) {
            $item->categories()->sync($request->categories);
        }

        return redirect('/mypage?page=sell');
    }
}
