<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function いいねした商品だけがマイリストに表示される()
    {
        $user = User::factory()->create();

        $likedItem = Item::factory()->create([
            'name' => 'いいねした商品',
        ]);

        $otherItem = Item::factory()->create([
            'name' => 'いいねしていない商品',
        ]);

        Favorite::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertSee($likedItem->name);

        $response->assertDontSee($otherItem->name);
    }

    /** @test */
    public function マイリスト内の購入済み商品には_sold_ラベルが表示される()
    {
        $user = User::factory()->create();

        $soldItem = Item::factory()->create([
            'name' => '購入済みの商品',
            'status' => 'sold',
        ]);

        Favorite::factory()->create([
            'user_id' => $user->id,
            'item_id' => $soldItem->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertSee($soldItem->name);

        $response->assertSee('sold');
    }

    /** @test */
    public function 未認証ユーザーがマイリストにアクセスした場合_いいね商品は表示されない()
    {
        $user = User::factory()->create();
        $likedItem = Item::factory()->create([
            'name' => '他人がいいねした商品',
        ]);

        Favorite::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $response = $this->get('/?tab=mylist');

        $response->assertDontSee($likedItem->name);
    }
}
