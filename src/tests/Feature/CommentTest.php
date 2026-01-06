<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン済みユーザーはコメントを送信できる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment' => 'テストコメントです',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => 'テストコメントです',
        ]);

        $this->assertEquals(1, Comment::where('item_id', $item->id)->count());
    }

    /** @test */
    public function ログインしていないユーザーはコメントを送信できない()
    {
        $item = Item::factory()->create();

        $response = $this->post("/item/{$item->id}/comment", [
            'comment' => 'ゲストのコメントです',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'comment' => 'ゲストのコメントです',
        ]);
    }

    /** @test */
    public function コメントが空の場合_バリデーションエラーになる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->from("/item/{$item->id}")
            ->actingAs($user)
            ->post("/item/{$item->id}/comment", [
                'comment' => '',
            ]);

        $response->assertRedirect("/item/{$item->id}");

        $response->assertSessionHasErrors([
            'comment' => 'コメントは必須です',
        ]);

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
        ]);
    }

    /** @test */
    public function コメントが255文字を超える場合_バリデーションエラーになる()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $longComment = str_repeat('あ', 256);

        $response = $this->from("/item/{$item->id}")
            ->actingAs($user)
            ->post("/item/{$item->id}/comment", [
                'comment' => $longComment,
            ]);

        $response->assertRedirect("/item/{$item->id}");

        $response->assertSessionHasErrors([
            'comment' => 'コメントは255文字以内で入力してください。',
        ]);

        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'comment' => $longComment,
        ]);
    }
}
