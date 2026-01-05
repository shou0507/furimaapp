<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'price',
        'brand_name',
        'description',
        'image_url',
        'condition',
        'status',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item', 'item_id', 'category_id')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // この商品についたお気に入り（中間テーブルモデル）
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // この商品をお気に入りしているユーザー一覧
    public function favoriteUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }
}
