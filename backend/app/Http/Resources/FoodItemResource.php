<?php  
namespace App\Http\Resources;  

use Illuminate\Http\Resources\Json\JsonResource;  

class FoodItemResource extends JsonResource  
{  
    public function toArray($request)  
    {  
        return [  
            'id' => $this->id,  
            'name' => $this->name,  
            'image' => $this->image,  
            'calories' => $this->calories,  
            'category_id' => $this->category_id  
        ];  
    }  
}  