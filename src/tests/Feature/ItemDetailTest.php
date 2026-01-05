<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function 商品詳細ページに必要な情報が表示される()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create([
            'name' => 'テスト商品',
            'brand_name' => 'テストブランド',
            'price' => '12345',
            'description' => 'これはテスト用の商品説明です。',
            'image_url' => 'https://example.com/test-item.jpg',
            'condition' => '良好',
            'status' => 'available',
        ]);

        $categories = Category::factory()->count(2)->create();
        $item->categories()->attach($categories->pluck('id'));

        Favorite::factory()->count(3)->create([
            'item_id' => $item->id,
        ]);

        $commentUser1 = User::factory()->create(['name' => 'コメントユーザー1']);
        $commentUser2 = User::factory()->create(['name' => 'コメントユーザー2']);

        Comment::factory()->create([
            'user_id' => $commentUser1->id,
            'item_id' => $item->id,
            'comment' => 'とても良い商品です',
        ]);

        Comment::factory()->create([
            'user_id' => $commentUser2->id,
            'item_id' => $item->id,
            'comment' => '購入を検討しています。',
        ]);

        $response = $this->actingAs($user)->get('/item/'.$item->id);

        $response->assertStatus(200);

        $response->assertSee('テスト商品');
        $response->assertSee('テストブランド');
        $response->assertSee('12,345');
        $response->assertSee('これはテスト用の商品説明です。');
        $response->assertSee('良好');
        $response->assertSee('https://example.com/test-item.jpg');

        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }

        $response->assertSee('3');
        $response->assertSee('2');

        $response->assertSee('コメントユーザー1');
        $response->assertSee('コメントユーザー2');
        $response->assertSee('とても良い商品です');
        $response->assertSee('購入を検討しています。');
    }

    /** @test */
    public function 複数選択されたカテゴリが商品詳細ページに表示される()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create([
            'name' => 'カテゴリテスト商品',
        ]);

        $categories = Category::factory()->count(3)->create();

        $item->categories()->attach($categories->pluck('id'));

        $response = $this->actingAs($user)->get('/item/'.$item->id);

        $response->assertStatus(200);

        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }
}
