<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 出品者のユーザーID
            $table->string('name', 255);            // 商品名
            $table->unsignedInteger('price');        // 価格
            $table->string('brand_name', 255)->nullable(); // ブランド名
            $table->text('description')->nullable(); // 説明文
            $table->string('image_url', 255);        // 商品画像URL
            $table->string('condition', 50);         // 商品状態
            $table->string('status', 50)->default('active'); // 商品ステータス
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
