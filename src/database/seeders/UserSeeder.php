<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 商品を持つユーザー
        User::factory()->create([
            'id' => 1,
            'name' => '出品者1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);

        // 商品を持つユーザー
        User::create([
            'id' => 2,
            'name' => '出品者2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        // 商品を持たないユーザー
        User::create([
            'id' => 3,
            'name' => 'ユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
