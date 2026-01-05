<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン済みユーザーはプロフィール画像ユーザー名出品商品購入商品一覧を確認できる()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'avatar' => null,
        ]);

        $sellingItems = Item::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'available',
        ]);

        Item::factory()->count(2)->create([
            'status' => 'sold',
        ]);

        // ===== 出品した商品タブ（デフォルト: sell） =====
        $response = $this->actingAs($user)->get('/mypage');

        $response->assertStatus(200);

        $response->assertSee('/images/default.png', false);

        $response->assertSeeText('テストユーザー');

        $response->assertSeeText('出品した商品');
        $response->assertSeeText('購入した商品');

        foreach ($sellingItems as $item) {
            $response->assertSeeText($item->name);
        }

        $responseBuy = $this->actingAs($user)->get('/mypage?page=buy');

        $responseBuy->assertStatus(200);

        $responseBuy->assertSeeText('購入した商品');

        $responseBuy->assertSee('items-grid');
    }

    /** @test */
    public function ログイン済みユーザーはプロフィール編集画面で過去の情報が初期値として表示される()
    {
        $user = \App\Models\User::factory()->create([
            'name' => '変更前ユーザー',
            'avatar' => 'avatars/before.png',
        ]);

        \DB::table('addresses')->insert([
            'user_id' => $user->id,
            'postal_code' => '123-4567',
            'address' => '東京都テスト区1-2-3',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/mypage/profile');

        $response->assertStatus(200);

        $response->assertSee('storage/'.$user->avatar, false);
        $response->assertSee('value="'.$user->name.'"', false);
        $response->assertSee('value="123-4567"', false);
        $response->assertSee('value="東京都テスト区1-2-3"', false);
    }

    /** @test */
    public function ログイン済みユーザーはカテゴリ付きで商品を出品でき保存内容が正しい()
    {
        $user = User::factory()->create();

        // 画像アップロードのテスト準備
        Storage::fake('public');
        $image = UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');

        // カテゴリを2つ作成（CategoryFactory がある前提）
        $categories = Category::factory()->count(2)->create();
        $categoryIds = $categories->pluck('id')->toArray();

        $this->actingAs($user);

        // 実際のフォームに合わせた入力データ
        $data = [
            'image' => $image,
            'categories' => $categoryIds,   // name="categories[]"
            'condition' => '良好',         // セレクトの value と合わせる
            'name' => 'カテゴリ付きテスト商品',
            'brand_name' => 'テストブランド',
            'description' => 'カテゴリ付きの商品です。',
            'price' => 5000,
        ];

        // /sell に POST（Blade の form action="/sell" と一致）
        $response = $this->post('/sell', $data);

        // バリデーションに通ってリダイレクトされていること
        $response->assertStatus(302);

        // 直近で作られた商品を取得
        $item = Item::latest()->first();
        $this->assertNotNull($item);

        // items テーブルの値を確認（カテゴリ以外）
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'カテゴリ付きテスト商品',
            'brand_name' => 'テストブランド',
            'description' => 'カテゴリ付きの商品です。',
            'price' => 5000,
            'condition' => '良好',
            'user_id' => $user->id,
        ]);

        // ★ ピボットテーブル category_item に、カテゴリとの紐付けが保存されていること
        foreach ($categoryIds as $categoryId) {
            $this->assertDatabaseHas('category_item', [
                'item_id' => $item->id,
                'category_id' => $categoryId,
            ]);
        }
    }
}
