<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodDiary extends Model
{
    protected $fillable = [
        'user_id',
        'user_profiles_id',
        'date',
        'meal_type',
        'notes'
    ];

    // Relasi ke tabel food_inputs
    public function foodInputs()
    {
        return $this->hasMany(FoodInput::class, 'food_diaries_id');
    }
}