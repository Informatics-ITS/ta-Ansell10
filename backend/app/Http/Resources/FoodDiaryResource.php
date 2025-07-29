<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FoodDiaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_profiles_id' => $this->user_profiles_id,
            'date' => $this->date,
            'meal_type' => $this->meal_type,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Include food inputs with their food items if loaded
            'food_inputs' => $this->whenLoaded('foodInputs', function () {
                return $this->foodInputs->map(function ($foodInput) {
                    return [
                        'id' => $foodInput->id,
                        'food_item_id' => $foodInput->food_item_id,
                        'portion_size' => $foodInput->portion_size,
                        'food_item' => $foodInput->foodItem ? [
                            'id' => $foodInput->foodItem->id,
                            'name' => $foodInput->foodItem->name,
                            'calories' => $foodInput->foodItem->calories,
                            'protein' => $foodInput->foodItem->protein,
                            'carbs' => $foodInput->foodItem->carbs,
                            'fat' => $foodInput->foodItem->fat,
                            'category_id' => $foodInput->foodItem->category_id,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
