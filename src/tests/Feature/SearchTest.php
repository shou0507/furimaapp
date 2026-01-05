<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function 商品名で部分一致検索ができる()
    {
        $matchItem = Item::factory()->create([
            'name' => 'テスト冷蔵庫',
        ]);

        $otherItem = Item::factory()->create([
            'name' => 'テスト洗濯機',
        ]);

        $keyword = '冷蔵';

        $response = $this->get('/?keyword=' . $keyword);

        $response->assertStatus(200);

        $response->assertSeeText($matchItem->name);

        $response->assertDontSeeText($otherItem->name);
    }

    /** @test */
    public function 検索状態がマイリストでも保持されている()
    {
        $user = User::factory()->create();

        $keyword = 'スマホ';

        $responseHome = $this->actingAs($user)->get('/?keyword=' . $keyword);

        $responseHome->assertStatus(200);

        $responseHome->assertSee('name="keyword"', false);
        $responseHome->assertSee('value="' . $keyword . '"', false);

        $responseMypage = $this->actingAs($user)->get('/mypage?keyword=' . $keyword);

        $responseMypage->assertStatus(200);

        $responseMypage->assertSee('name="keyword"', false);
        $responseMypage->assertSee('value="' . $keyword . '"', false);
    }
}
