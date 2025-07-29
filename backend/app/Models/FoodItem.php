<?php  
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodItem extends Model
{
    protected $fillable = [
        'category_id', 'name', 'image', 
        'calories', 'protein', 'carbs', 'fat'
    ];

    // Relasi dengan kategori makanan
    public function category() 
    {
        return $this->belongsTo(FoodCategory::class); 
    }

    // Relasi dengan food inputs (bukan langsung dengan food diaries)
    public function foodInputs() 
    {
        return $this->hasMany(FoodInput::class);
    }

    // Jika Anda ingin mencari food items yang ada pada food_diaries lewat food_inputs
    public function foodDiaries()
    {
        return $this->belongsToMany(FoodDiary::class, 'food_inputs');
    }
}
