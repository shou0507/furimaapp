<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 商品一覧（トップ）
Route::get('/', [ItemController::class, 'index']);

Route::get('/item/{item}', [ItemController::class, 'show']);

Route::get('/sell', [SellController::class, 'create']);

Route::post('/sell', [SellController::class, 'store']);

// プロフィール編集画面
Route::get('/mypage/profile', [MyPageController::class, 'edit']);

// プロフィール更新処理
Route::post('/mypage/profile', [MyPageController::class, 'update']);

Route::get('/mypage', [ProfileController::class, 'sellingItems']);

Route::get('/purchase/{item_id}', [PurchaseController::class, 'purchase']);

Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'address']);

Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress']);

Route::post('/purchase/{item_id}/checkout', [PurchaseController::class, 'checkout']);

Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'success']);

Route::get('/purchase/{item_id}/cancel', [PurchaseController::class, 'cancel']);

Route::get('/favorite/toggle/{item}', [ItemController::class, 'toggleFavorite'])
    ->middleware('auth');

Route::post('/item/{item}/comment', [ItemController::class, 'storeComment'])
    ->middleware('auth');
