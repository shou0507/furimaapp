<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function 全商品が一覧に表示される()
    {
        $items = Item::factory()->count(3)->create();

        $response = $this->get('/');

        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }

    /** @test */
    public function 購入済みの商品には_sold_ラベルが表示される()
    {
        $soldItem = Item::factory()->create([
            'name' => '商品',
            'status' => 'sold',
        ]);

        $response = $this->get('/');

        $response->assertSee($soldItem->name);

        $response->assertSee('sold');

    }

    /** @test */
    public function ログインユーザーが出品した商品は一覧に表示されない()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myItem = Item::factory()->create([
            'name' => '自分の商品',
            'user_id' => $user->id,
        ]);

        $othersItem = Item::factory()->create([
            'name' => '他人の商品',
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertSee($othersItem->name);

        $response->assertDontSee($myItem->name);
    }
}
