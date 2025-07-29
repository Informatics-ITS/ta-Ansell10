<?php  
namespace App\Models;  

use Illuminate\Database\Eloquent\Model;  

class FoodCategory extends Model  
{  
    protected $fillable = ['name', 'icon'];  

    public function foodItems()  
    {  
        return $this->hasMany(FoodItem::class, 'category_id');  
    }  
}  