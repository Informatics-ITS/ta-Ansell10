<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\FoodDiary;
use App\Models\FoodInput;  // Menambahkan model FoodInput
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\FoodCategoryResource;
use App\Http\Resources\FoodItemResource;
use App\Http\Resources\FoodDiaryResource;

class FoodDiaryController extends Controller
{
    // Get all food categories
    public function getCategories()
    {
        Log::info('Fetching all food categories.');

        $categories = FoodCategory::all();

        Log::info('Fetched categories:', ['categories' => $categories]);

        return FoodCategoryResource::collection($categories);
    }

    // Get all food items (semua makanan tanpa kategori)
    public function getAllFoodItems()
    {
        Log::info('Fetching all food items.');

        // Mengambil semua makanan tanpa filter kategori
        $foodItems = FoodItem::all();

        Log::info('Fetched all food items:', ['foodItems' => $foodItems]);

        return FoodItemResource::collection($foodItems);
    }


    // Get food items by category
    public function getFoodItemsByCategory($categoryId)
    {
        Log::info('Fetching food items for category ID: ' . $categoryId);

        $foodItems = FoodItem::where('category_id', $categoryId)->get();

        Log::info('Fetched food items:', ['foodItems' => $foodItems]);

        return FoodItemResource::collection($foodItems);
    }

    // Store food diary entry
   public function storeFoodDiaryEntry(Request $request)
{
    Log::info('Storing food diary entry. Request data:', $request->all());

    // Log sebelum validasi
    Log::info('Validating food diary entry data...');

    try {
        // Validasi input untuk banyak food_item dan portion_size
        $validatedData = $request->validate([
            'user_profiles_id' => 'required|exists:user_profiles,id',  // Memastikan user_profiles_id valid
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'food_inputs' => 'required|array|min:1',  // Array of food items
            'food_inputs.*.food_item_id' => 'required|exists:food_items,id', // Memastikan food_item_id valid
            'food_inputs.*.portion_size' => 'nullable|numeric|min:0.01',  // Optional portion size
            'notes' => 'nullable|string|max:500'
        ]);

        // Menambahkan user_id ke dalam validated data
        $validatedData['user_id'] = $request->user()->id;  // Menggunakan user yang sedang login

        // Log setelah validasi berhasil
        Log::info('Validation passed:', ['user_id' => $request->user()->id]);  // Log dengan user_id dalam array
        Log::info('Validated data:', $validatedData);  // Log data yang telah divalidasi

        // Create food diary entry (food_diaries)
        $foodDiary = FoodDiary::create([
            'user_id' => $validatedData['user_id'],  // Menggunakan user_id dari data yang telah divalidasi
            'user_profiles_id' => $validatedData['user_profiles_id'],  // Menggunakan user_profiles_id yang dikirim dari frontend
            'date' => $validatedData['date'],
            'meal_type' => $validatedData['meal_type'],
            'notes' => $validatedData['notes'] ?? null
        ]);

       // Insert food items into food_inputs
foreach ($validatedData['food_inputs'] as $foodItemData) {
    // Menyimpan data food_input dengan food_diaries_id yang baru saja dibuat
    FoodInput::create([
        'food_diaries_id' => $foodDiary->id,  // Pastikan food_diaries_id disertakan
        'food_item_id' => $foodItemData['food_item_id'],
        'portion_size' => $foodItemData['portion_size'] ?? 1
    ]);
}


        Log::info('Food diary entry created successfully:', ['foodDiary' => $foodDiary]);

        return new FoodDiaryResource($foodDiary);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation failed:', ['errors' => $e->errors()]);
        return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Error storing food diary entry:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'An error occurred while saving the food diary entry.'], 500);
    }
}





    // Get all food diary entries for the authenticated user
    public function getFoodDiaryEntries(Request $request)
    {
        Log::info('Fetching all food diary entries for the authenticated user.');

        // Mengambil semua entri diary dengan relasi ke food_item dan food_input
        $entries = FoodDiary::where('user_profiles_id', Auth::id())
            ->with(['foodInputs.foodItem'])  // Mengambil data terkait food_inputs dan food_item
            ->get();

        Log::info('Fetched food diary entries:', ['entries' => $entries]);

        return FoodDiaryResource::collection($entries);
    }

    // Get a single food diary entry by ID
    public function getFoodDiaryEntry($id)
    {
        Log::info('Fetching food diary entry for ID: ' . $id);

        // Mengambil satu entri diary beserta food_inputs yang terkait
        $entry = FoodDiary::where('user_profiles_id', Auth::id())
            ->with(['foodInputs.foodItem'])
            ->findOrFail($id);

        Log::info('Fetched food diary entry:', ['entry' => $entry]);

        return new FoodDiaryResource($entry);
    }

    // Update a food diary entry
    public function updateFoodDiaryEntry(Request $request, $id)
    {
        Log::info('Updating food diary entry for ID: ' . $id);

        // Validasi input untuk banyak food_item dan portion_size
        $validatedData = $request->validate([
            'date' => 'required|date',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'food_items' => 'required|array|min:1',
            'food_items.*.food_item_id' => 'required|exists:food_items,id',
            'food_items.*.portion_size' => 'nullable|numeric|min:0.5',
            'notes' => 'nullable|string|max:500'
        ]);

        Log::info('Validated data for updating food diary entry:', $validatedData);

        $foodDiary = FoodDiary::where('user_profiles_id',$request->user()->id);

        // Update food diary entry
        $foodDiary->update([
            'date' => $validatedData['date'],
            'meal_type' => $validatedData['meal_type'],
            'notes' => $validatedData['notes'] ?? null
        ]);

        // Hapus food inputs lama dan tambahkan yang baru
        $foodDiary->foodInputs()->delete();  // Hapus food inputs lama
        foreach ($validatedData['food_items'] as $foodItemData) {
            FoodInput::create([
                'food_diaries_id' => $foodDiary->id,
                'food_item_id' => $foodItemData['food_item_id'],
                'portion_size' => $foodItemData['portion_size'] ?? 1
            ]);
        }

        Log::info('Food diary entry updated successfully:', ['foodDiary' => $foodDiary]);

        return new FoodDiaryResource($foodDiary);
    }

    // Delete a food diary entry
    public function deleteFoodDiaryEntry($id)
{
    Log::info('Deleting food diary entry for ID: ' . $id);

    $user = Auth::user();

    // Ambil daftar semua ID profile milik user ini
    $userProfileIds = $user->profiles()->pluck('id')->toArray();

    // Cari food diary berdasarkan ID dan pastikan profile-nya milik user ini
    $foodDiary = FoodDiary::where('id', $id)
        ->whereIn('user_profiles_id', $userProfileIds)
        ->firstOrFail();

    $foodDiary->delete();

    Log::info('Food diary entry deleted successfully:', ['foodDiaryId' => $id]);

    return response()->json(['message' => 'Food diary entry deleted successfully.']);
}


    public function getFoodDiaryByDate(Request $request)
    {
        $date = $request->input('date');
        $userId = $request->input('user_profiles_id');
        Log::info('Fetching food diary entries for date:', ['date' => $date]);
        Log::info('Fetching food diary entries userId', ['userId' => $userId]);

        $foodDiary = FoodDiary::where('user_profiles_id', $userId)->where('date', $date) // assuming you have a scope or renamed column
            ->with(['foodInputs.foodItem'])
            ->get();
        Log::info('Fetched food diary entries for date:', ['entries' => $foodDiary]);
        return FoodDiaryResource::collection($foodDiary);
    }


}