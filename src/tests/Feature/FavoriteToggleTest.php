<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function いいねアイコン押下で_お気に入り登録され_いいね数が増える()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create();

        $this->actingAs($user)->get('/favorite/toggle/'.$item->id);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get('/item/'.$item->id);
        $response->assertStatus(200);

        $response->assertSee('1');

        $response->assertSee('/img/ハートロゴ_ピンク.png');
    }

    /** @test */
    public function いいね済み商品のアイコンは_ピンクに変化する()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create();

        $responseBefore = $this->actingAs($user)->get('/item/'.$item->id);
        $responseBefore->assertStatus(200);
        $responseBefore->assertSee('/img/ハートロゴ_デフォルト.png');

        $this->actingAs($user)->get('/favorite/toggle/'.$item->id);

        $responseAfter = $this->actingAs($user)->get('/item/'.$item->id);
        $responseAfter->assertStatus(200);

        $responseAfter->assertSee('/img/ハートロゴ_ピンク.png');
    }

    /** @test */
    public function いいねアイコンを再度押下すると_いいね解除され_いいね数が減る()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create();

        Favorite::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)->get('/favorite/toggle/'.$item->id);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get('/item/'.$item->id);
        $response->assertStatus(200);

        $response->assertSee('0');

        $response->assertSee('/img/ハートロゴ_デフォルト.png');
    }
}
