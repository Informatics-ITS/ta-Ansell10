<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodInput extends Model
{
    protected $fillable = [
        'food_diaries_id',
        'food_item_id',
        'portion_size'
    ];

    // Relasi ke tabel food_item
    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class, 'food_item_id');
    }

    // Relasi ke tabel food_diary
    public function foodDiary()
    {
        return $this->belongsTo(FoodDiary::class, 'food_diaries_id');
    }
}