<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;

class MyPageController extends Controller
{
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
