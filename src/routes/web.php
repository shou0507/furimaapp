<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RatingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 商品一覧（トップ）
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品詳細
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');


// ---------- ログイン不要 ----------

// 商品出品
Route::get('/sell', [SellController::class, 'create'])->middleware('auth');
Route::post('/sell', [SellController::class, 'store'])->middleware('auth');

// コメント
Route::post('/item/{item}/comment', [ItemController::class, 'storeComment'])
    ->middleware('auth');

// お気に入り
Route::get('/favorite/toggle/{item}', [ItemController::class, 'toggleFavorite'])
    ->middleware('auth');


// ---------- 購入処理 ----------
Route::middleware('auth')->group(function () {

    Route::get('/purchase/{item_id}', [PurchaseController::class, 'purchase']);
    
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'address']);
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress']);

    Route::post('/purchase/{item_id}/checkout', [PurchaseController::class, 'checkout']);

    Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'success']);
    Route::get('/purchase/{item_id}/cancel', [PurchaseController::class, 'cancel']);
});


// ---------- マイページ ----------
Route::middleware('auth')->group(function () {

    // マイページ
    Route::get('/mypage', [MyPageController::class, 'index'])
        ->name('mypage.index');

    // プロフィール編集
    Route::get('/mypage/profile', [MyPageController::class, 'edit'])
        ->name('mypage.profile.edit');

    // プロフィール更新
    Route::post('/mypage/profile', [MyPageController::class, 'update'])
        ->name('mypage.profile.update');
});


// ---------- 取引 ----------
Route::middleware('auth')->group(function () {

    // 取引画面
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');

    // メッセージ送信
    Route::post('/transactions/{transaction}/messages', [TransactionController::class, 'storeMessage'])
        ->name('transactions.messages.store');

    // メッセージ編集
    Route::put('/transactions/{transaction}/messages/{message}', [TransactionController::class, 'updateMessage'])
        ->name('transactions.messages.update');

    // メッセージ削除
    Route::delete('/transactions/{transaction}/messages/{message}', [TransactionController::class, 'destroyMessage'])
        ->name('transactions.messages.destroy');

    // 取引完了
    Route::post('/transactions/{transaction}/complete', [TransactionController::class, 'complete'])
        ->name('transactions.complete');

    // 評価
    Route::post('/transactions/{transaction}/rating', [RatingController::class, 'store'])
        ->name('transactions.rating.store');
});