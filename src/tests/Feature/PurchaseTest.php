<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 購入ボタン押下で_商品が購入済みステータスになる()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'status' => 'active',
        ]);

        // 購入者の住所を作成（factory がなければ Address::create でもOK）
        $address = Address::create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => 'テスト住所',
            'building' => 'テストビル',
        ]);

        $response = $this->actingAs($buyer)->post("/purchase/{$item->id}/checkout", [
            'payment_method' => 'konbini',
            'address_id' => $address->id,  // 👈 これが PurchaseRequest の必須項目
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'sold',
        ]);
    }

    /** @test */
    public function 購入した商品は_商品一覧で_sold_と表示される()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '購入テスト商品',
            'status' => 'active',
        ]);

        $address = Address::create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => 'テスト住所',
            'building' => 'テストビル',
        ]);

        $this->actingAs($buyer)->post("/purchase/{$item->id}/checkout", [
            'payment_method' => 'konbini',
            'address_id' => $address->id,
        ]);

        $response = $this->actingAs($buyer)->get('/');

        $response->assertStatus(200);
        $response->assertSee('購入テスト商品');

        // Blade 側の表記に合わせて 'sold' / 'Sold' に調整
        $response->assertSee('sold');
    }

    /** @test */
    public function 購入した商品が_プロフィールの購入商品一覧に表示される()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => 'プロフィール購入テスト商品',
            'status' => 'active',
        ]);

        $address = Address::create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => 'テスト住所',
            'building' => 'テストビル',
        ]);

        $this->actingAs($buyer)->post("/purchase/{$item->id}/checkout", [
            'payment_method' => 'konbini',
            'address_id' => $address->id,
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?page=buy');

        $response->assertStatus(200);
        $response->assertSee('プロフィール購入テスト商品');
    }

    /** @test */
    public function 支払い方法を選択すると_小計画面に選択内容が反映される()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'status' => 'active',
        ]);

        $address = \App\Models\Address::create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-2-3',
            'building' => 'テストビル101',
        ]);

        $patterns = [
            'konbini' => 'コンビニ支払い',
            'card' => 'カード支払い',
        ];

        foreach ($patterns as $value => $label) {

            $response = $this->actingAs($buyer)
                ->withSession([
                    '_old_input' => [
                        'payment_method' => $value,
                        'address_id' => $address->id,
                    ],
                ])
                ->get("/purchase/{$item->id}");

            $response->assertStatus(200);

            $response->assertSee($label);
        }
    }

    /** @test */
    public function 送付先住所を変更すると_商品購入画面に新しい住所が表示される()
    {
        $seller = User::factory()->create();

        $buyer = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($buyer)->post("/purchase/address/{$item->id}", [
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市1-2-3',
            'building' => 'テストビル101',
        ]);

        $response->assertRedirect("/purchase/{$item->id}");

        $page = $this->actingAs($buyer)->get("/purchase/{$item->id}");

        $page->assertStatus(200);

        $page->assertSee('123-4567');
        $page->assertSee('テスト県テスト市1-2-3');
        $page->assertSee('テストビル101');
    }

    /** @test */
    public function 購入した商品に_送付先住所が紐づいて登録される()
    {
        $seller = User::factory()->create();

        $buyer = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'status' => 'active',
        ]);

        $address = Address::create([
            'user_id' => $buyer->id,
            'postal_code' => '987-6543',
            'address' => '別のテスト県別のテスト市9-8-7',
            'building' => '別ビル202',
        ]);

        $response = $this->actingAs($buyer)->post("/purchase/{$item->id}/checkout", [
            'payment_method' => 'konbini',
            'address_id' => $address->id,
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'address_id' => $address->id,
        ]);
    }
}
