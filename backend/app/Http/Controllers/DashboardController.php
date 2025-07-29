<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getNutritionData(Request $request)
    {
        $userProfileId = $request->input('user_profiles_id');

        return response()->json([
            'success' => true,
            'nutrition' => [
                'daily' => $this->getDailyNutrition($userProfileId),
                'weekly' => $this->getWeeklyNutrition($userProfileId),
                'monthly' => $this->getMonthlyNutrition($userProfileId),
                'last30Days' => $this->getLast30DaysNutrition($userProfileId)
            ]
        ]);
    }

    /**
     * Get daily nutrition (today vs yesterday)
     */
    private function getDailyNutrition(int $userProfileId): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayNutrition = $this->getNutritionForDate($userProfileId, $today);
        $yesterdayNutrition = $this->getNutritionForDate($userProfileId, $yesterday);

        return [
            'today' => $todayNutrition,
            'yesterday' => $yesterdayNutrition,
            'percentage_changes' => [
                'calories' => $this->calculatePercentageChange($yesterdayNutrition['calories'], $todayNutrition['calories']),
                'protein' => $this->calculatePercentageChange($yesterdayNutrition['protein'], $todayNutrition['protein']),
                'carbs' => $this->calculatePercentageChange($yesterdayNutrition['carbs'], $todayNutrition['carbs']),
                'fat' => $this->calculatePercentageChange($yesterdayNutrition['fat'], $todayNutrition['fat'])
            ],
            'date' => $today->format('Y-m-d')
        ];
    }

    /**
     * Get weekly nutrition (current week)
     */
    private function getWeeklyNutrition(int $userProfileId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $currentWeek = $this->getNutritionForDateRange($userProfileId, $startOfWeek, $endOfWeek);
        $lastWeek = $this->getNutritionForDateRange($userProfileId, $lastWeekStart, $lastWeekEnd);

        // Get daily breakdown for current week
        $dailyBreakdown = [];
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $dailyBreakdown[$date->format('Y-m-d')] = $this->getNutritionForDate($userProfileId, $date);
        }

        return [
            'current_week' => $currentWeek,
            'last_week' => $lastWeek,
            'daily_breakdown' => $dailyBreakdown,
            'percentage_changes' => [
                'calories' => $this->calculatePercentageChange($lastWeek['calories'], $currentWeek['calories']),
                'protein' => $this->calculatePercentageChange($lastWeek['protein'], $currentWeek['protein']),
                'carbs' => $this->calculatePercentageChange($lastWeek['carbs'], $currentWeek['carbs']),
                'fat' => $this->calculatePercentageChange($lastWeek['fat'], $currentWeek['fat'])
            ],
            'week_start' => $startOfWeek->format('Y-m-d'),
            'week_end' => $endOfWeek->format('Y-m-d')
        ];
    }

    /**
     * Get monthly nutrition (current month)
     */
    private function getMonthlyNutrition(int $userProfileId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $currentMonth = $this->getNutritionForDateRange($userProfileId, $startOfMonth, $endOfMonth);
        $lastMonth = $this->getNutritionForDateRange($userProfileId, $lastMonthStart, $lastMonthEnd);

        // Get weekly breakdown for current month
        $weeklyBreakdown = [];
        $weekStart = $startOfMonth->copy();
        $weekNumber = 1;

        while ($weekStart <= $endOfMonth) {
            $weekEnd = $weekStart->copy()->addDays(6);
            if ($weekEnd > $endOfMonth) {
                $weekEnd = $endOfMonth->copy();
            }

            $weeklyNutrition = $this->getNutritionForDateRange($userProfileId, $weekStart, $weekEnd);
            $weeklyNutrition['week'] = $weekNumber;
            $weeklyNutrition['week_start'] = $weekStart->format('Y-m-d');
            $weeklyNutrition['week_end'] = $weekEnd->format('Y-m-d');

            $weeklyBreakdown[] = $weeklyNutrition;

            $weekStart->addDays(7);
            $weekNumber++;
        }

        return [
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'weekly_breakdown' => $weeklyBreakdown,
            'percentage_changes' => [
                'calories' => $this->calculatePercentageChange($lastMonth['calories'], $currentMonth['calories']),
                'protein' => $this->calculatePercentageChange($lastMonth['protein'], $currentMonth['protein']),
                'carbs' => $this->calculatePercentageChange($lastMonth['carbs'], $currentMonth['carbs']),
                'fat' => $this->calculatePercentageChange($lastMonth['fat'], $currentMonth['fat'])
            ],
            'month_start' => $startOfMonth->format('Y-m-d'),
            'month_end' => $endOfMonth->format('Y-m-d')
        ];
    }

    /**
     * Get last 30 days nutrition
     */
    private function getLast30DaysNutrition(int $userProfileId): array
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(29); // 30 days including today

        $totalNutrition = $this->getNutritionForDateRange($userProfileId, $startDate, $endDate);

        // Get daily data for last 30 days
        $dailyData = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dailyNutrition = $this->getNutritionForDate($userProfileId, $date);
            $dailyNutrition['date'] = $date->format('Y-m-d');
            $dailyData[] = $dailyNutrition;
        }

        // Calculate averages
        $averages = [
            'daily_calories' => round($totalNutrition['calories'] / 30, 2),
            'daily_protein' => round($totalNutrition['protein'] / 30, 2),
            'daily_carbs' => round($totalNutrition['carbs'] / 30, 2),
            'daily_fat' => round($totalNutrition['fat'] / 30, 2)
        ];

        return [
            'totals' => $totalNutrition,
            'averages' => $averages,
            'daily_data' => $dailyData,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d')
        ];
    }

    /**
     * Get nutrition data for a specific date
     */
    private function getNutritionForDate(int $userProfileId, Carbon $date): array
    {
        $nutrition = DB::table('food_diaries')
            ->join('food_inputs', 'food_diaries.id', '=', 'food_inputs.food_diaries_id')
            ->join('food_items', 'food_inputs.food_item_id', '=', 'food_items.id')
            ->where('food_diaries.user_profiles_id', $userProfileId)
            ->whereDate('food_diaries.date', $date->format('Y-m-d'))
            ->selectRaw('
                SUM(food_items.calories * food_inputs.portion_size) as total_calories,
                SUM(food_items.protein * food_inputs.portion_size) as total_protein,
                SUM(food_items.carbs * food_inputs.portion_size) as total_carbs,
                SUM(food_items.fat * food_inputs.portion_size) as total_fat
            ')
            ->first();

        return [
            'calories' => round($nutrition->total_calories ?? 0, 2),
            'protein' => round($nutrition->total_protein ?? 0, 2),
            'carbs' => round($nutrition->total_carbs ?? 0, 2),
            'fat' => round($nutrition->total_fat ?? 0, 2),
            'meal_breakdown' => $this->getMealBreakdownForDate($userProfileId, $date)
        ];
    }

    /**
     * Get nutrition data for a date range
     */
    private function getNutritionForDateRange(int $userProfileId, Carbon $startDate, Carbon $endDate): array
    {
        $nutrition = DB::table('food_diaries')
            ->join('food_inputs', 'food_diaries.id', '=', 'food_inputs.food_diaries_id')
            ->join('food_items', 'food_inputs.food_item_id', '=', 'food_items.id')
            ->where('food_diaries.user_profiles_id', $userProfileId)
            ->whereBetween('food_diaries.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('
                SUM(food_items.calories * food_inputs.portion_size) as total_calories,
                SUM(food_items.protein * food_inputs.portion_size) as total_protein,
                SUM(food_items.carbs * food_inputs.portion_size) as total_carbs,
                SUM(food_items.fat * food_inputs.portion_size) as total_fat
            ')
            ->first();

        return [
            'calories' => round($nutrition->total_calories ?? 0, 2),
            'protein' => round($nutrition->total_protein ?? 0, 2),
            'carbs' => round($nutrition->total_carbs ?? 0, 2),
            'fat' => round($nutrition->total_fat ?? 0, 2)
        ];
    }

    /**
     * Get meal breakdown for a specific date
     */
    private function getMealBreakdownForDate(int $userProfileId, Carbon $date): array
    {
        $meals = DB::table('food_diaries')
            ->join('food_inputs', 'food_diaries.id', '=', 'food_inputs.food_diaries_id')
            ->join('food_items', 'food_inputs.food_item_id', '=', 'food_items.id')
            ->where('food_diaries.user_profiles_id', $userProfileId)
            ->whereDate('food_diaries.date', $date->format('Y-m-d'))
            ->groupBy('food_diaries.meal_type')
            ->selectRaw('
                food_diaries.meal_type,
                SUM(food_items.calories * food_inputs.portion_size) as calories,
                SUM(food_items.protein * food_inputs.portion_size) as protein,
                SUM(food_items.carbs * food_inputs.portion_size) as carbs,
                SUM(food_items.fat * food_inputs.portion_size) as fat
            ')
            ->get();

        $breakdown = [];
        foreach ($meals as $meal) {
            $breakdown[$meal->meal_type] = [
                'calories' => round($meal->calories, 2),
                'protein' => round($meal->protein, 2),
                'carbs' => round($meal->carbs, 2),
                'fat' => round($meal->fat, 2)
            ];
        }

        // Ensure all meal types are present
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];
        foreach ($mealTypes as $mealType) {
            if (!isset($breakdown[$mealType])) {
                $breakdown[$mealType] = [
                    'calories' => 0,
                    'protein' => 0,
                    'carbs' => 0,
                    'fat' => 0
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }

    /**
     * Get nutrition recommendations based on user profile
     */
    public function getNutritionRecommendations(Request $request)
    {
        $userProfileId = $request->input('user_profiles_id');

        if (!$userProfileId) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found'
            ], 404);
        }

        // Get user profile data
        $profile = DB::table('user_profiles')->where('id', $userProfileId)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found'
            ], 404);
        }

        // Calculate daily nutrition recommendations
        $bmr = $profile->bmr ?? $this->calculateBMR($profile);
        $activityMultiplier = $this->getActivityMultiplier($profile->activity_level ?? 'sedentary');
        $dailyCalories = round($bmr * $activityMultiplier);

        // Macronutrient distribution (example: 50% carbs, 20% protein, 30% fat)
        $recommendations = [
            'daily_calories' => $dailyCalories,
            'protein' => [
                'grams' => round(($dailyCalories * 0.20) / 4), // 4 calories per gram
                'calories' => round($dailyCalories * 0.20),
                'percentage' => 20
            ],
            'carbs' => [
                'grams' => round(($dailyCalories * 0.50) / 4), // 4 calories per gram
                'calories' => round($dailyCalories * 0.50),
                'percentage' => 50
            ],
            'fat' => [
                'grams' => round(($dailyCalories * 0.30) / 9), // 9 calories per gram
                'calories' => round($dailyCalories * 0.30),
                'percentage' => 30
            ]
        ];

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
            'profile' => [
                'bmr' => $bmr,
                'activity_level' => $profile->activity_level ?? 'sedentary',
                'weight' => $profile->weight ?? 70,
                'height' => $profile->height ?? 170,
                'age' => $profile->age ?? 25,
                'gender' => $profile->gender ?? 'male'
            ]
        ]);
    }

    /**
     * Calculate BMR if not stored in profile
     */
    private function calculateBMR($profile): float
    {
        $weight = $profile->weight ?? 70;
        $height = $profile->height ?? 170;
        $age = $profile->age ?? 25;
        $gender = $profile->gender ?? 'male';

        if ($gender === 'male') {
            return 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
        } else {
            return 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
        }
    }

    /**
     * Get activity multiplier based on activity level
     */
    private function getActivityMultiplier(string $activityLevel): float
    {
        $multipliers = [
            'sedentary' => 1.2,
            'lightly_active' => 1.375,
            'moderately_active' => 1.55,
            'very_active' => 1.725,
            'extremely_active' => 1.9
        ];

        return $multipliers[$activityLevel] ?? 1.2;
    }

    public function getCaloriesData(Request $request)
    {
        $userProfileId = $request->input('user_profiles_id');

        return response()->json([
            'calories' => [
                'daily' => $this->getDailyCalories($userProfileId),
                'weekly' => $this->getWeeklyCalories($userProfileId),
                'monthly' => $this->getMonthlyCalories($userProfileId),
                'last30Days' => $this->getLast30DaysCalories($userProfileId)
            ]
        ]);
    }

    /**
     * Get daily calories for today (intake vs burned)
     */
    private function getDailyCalories(int $userProfileId): array
    {
        $today = Carbon::today();

        // Calories intake from food
        $todayIntake = $this->getCaloriesIntakeForDate($userProfileId, $today);

        // Calories burned from exercise
        $todayBurned = $this->getCaloriesBurnedForDate($userProfileId, $today);

        // Net calories (intake - burned)
        $todayNet = $todayIntake - $todayBurned;

        // Yesterday comparison
        $yesterday = Carbon::yesterday();
        $yesterdayIntake = $this->getCaloriesIntakeForDate($userProfileId, $yesterday);
        $yesterdayBurned = $this->getCaloriesBurnedForDate($userProfileId, $yesterday);
        $yesterdayNet = $yesterdayIntake - $yesterdayBurned;

        // Percentage changes
        $intakeChange = $yesterdayIntake > 0
            ? round((($todayIntake - $yesterdayIntake) / $yesterdayIntake) * 100, 2)
            : 0;

        $burnedChange = $yesterdayBurned > 0
            ? round((($todayBurned - $yesterdayBurned) / $yesterdayBurned) * 100, 2)
            : 0;

        $netChange = $yesterdayNet != 0
            ? round((($todayNet - $yesterdayNet) / abs($yesterdayNet)) * 100, 2)
            : 0;

        return [
            'today' => [
                'intake' => round($todayIntake, 2),
                'burned' => round($todayBurned, 2),
                'net' => round($todayNet, 2)
            ],
            'yesterday' => [
                'intake' => round($yesterdayIntake, 2),
                'burned' => round($yesterdayBurned, 2),
                'net' => round($yesterdayNet, 2)
            ],
            'percentage_changes' => [
                'intake' => $intakeChange,
                'burned' => $burnedChange,
                'net' => $netChange
            ],
            'date' => $today->format('Y-m-d')
        ];
    }

    /**
     * Get weekly calories (current week vs previous week)
     */
    private function getWeeklyCalories(int $userProfileId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Current week totals
        $currentWeekIntake = $this->getCaloriesIntakeForDateRange($userProfileId, $startOfWeek, $endOfWeek);
        $currentWeekBurned = $this->getCaloriesBurnedForDateRange($userProfileId, $startOfWeek, $endOfWeek);
        $currentWeekNet = $currentWeekIntake - $currentWeekBurned;

        // Previous week totals
        $startOfPrevWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfPrevWeek = Carbon::now()->subWeek()->endOfWeek();

        $previousWeekIntake = $this->getCaloriesIntakeForDateRange($userProfileId, $startOfPrevWeek, $endOfPrevWeek);
        $previousWeekBurned = $this->getCaloriesBurnedForDateRange($userProfileId, $startOfPrevWeek, $endOfPrevWeek);
        $previousWeekNet = $previousWeekIntake - $previousWeekBurned;

        // Daily breakdown for current week
        $dailyBreakdown = [];
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dayIntake = $this->getCaloriesIntakeForDate($userProfileId, $date);
            $dayBurned = $this->getCaloriesBurnedForDate($userProfileId, $date);

            $dailyBreakdown[$dateKey] = [
                'intake' => round($dayIntake, 2),
                'burned' => round($dayBurned, 2),
                'net' => round($dayIntake - $dayBurned, 2),
                'day_name' => $date->format('l')
            ];
        }

        // Percentage changes
        $intakeChange = $previousWeekIntake > 0
            ? round((($currentWeekIntake - $previousWeekIntake) / $previousWeekIntake) * 100, 2)
            : 0;

        $burnedChange = $previousWeekBurned > 0
            ? round((($currentWeekBurned - $previousWeekBurned) / $previousWeekBurned) * 100, 2)
            : 0;

        $netChange = $previousWeekNet != 0
            ? round((($currentWeekNet - $previousWeekNet) / abs($previousWeekNet)) * 100, 2)
            : 0;

        return [
            'current_week' => [
                'intake' => round($currentWeekIntake, 2),
                'burned' => round($currentWeekBurned, 2),
                'net' => round($currentWeekNet, 2)
            ],
            'previous_week' => [
                'intake' => round($previousWeekIntake, 2),
                'burned' => round($previousWeekBurned, 2),
                'net' => round($previousWeekNet, 2)
            ],
            'percentage_changes' => [
                'intake' => $intakeChange,
                'burned' => $burnedChange,
                'net' => $netChange
            ],
            'daily_breakdown' => $dailyBreakdown,
            'week_range' => [
                'start' => $startOfWeek->format('Y-m-d'),
                'end' => $endOfWeek->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get monthly calories (current month vs previous month)
     */
    private function getMonthlyCalories(int $userProfileId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Current month totals
        $currentMonthIntake = $this->getCaloriesIntakeForDateRange($userProfileId, $startOfMonth, $endOfMonth);
        $currentMonthBurned = $this->getCaloriesBurnedForDateRange($userProfileId, $startOfMonth, $endOfMonth);
        $currentMonthNet = $currentMonthIntake - $currentMonthBurned;

        // Previous month totals
        $startOfPrevMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfPrevMonth = Carbon::now()->subMonth()->endOfMonth();

        $previousMonthIntake = $this->getCaloriesIntakeForDateRange($userProfileId, $startOfPrevMonth, $endOfPrevMonth);
        $previousMonthBurned = $this->getCaloriesBurnedForDateRange($userProfileId, $startOfPrevMonth, $endOfPrevMonth);
        $previousMonthNet = $previousMonthIntake - $previousMonthBurned;

        // Weekly breakdown for current month
        $weeklyBreakdown = [];
        $currentDate = $startOfMonth->copy();
        $weekNumber = 1;

        while ($currentDate <= $endOfMonth) {
            $weekStart = $currentDate->copy();
            $weekEnd = $currentDate->copy()->addDays(6)->min($endOfMonth);

            $weekIntake = $this->getCaloriesIntakeForDateRange($userProfileId, $weekStart, $weekEnd);
            $weekBurned = $this->getCaloriesBurnedForDateRange($userProfileId, $weekStart, $weekEnd);

            $weeklyBreakdown[] = [
                'week' => $weekNumber,
                'intake' => round($weekIntake, 2),
                'burned' => round($weekBurned, 2),
                'net' => round($weekIntake - $weekBurned, 2),
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d')
            ];

            $currentDate->addWeek();
            $weekNumber++;
        }

        // Percentage changes
        $intakeChange = $previousMonthIntake > 0
            ? round((($currentMonthIntake - $previousMonthIntake) / $previousMonthIntake) * 100, 2)
            : 0;

        $burnedChange = $previousMonthBurned > 0
            ? round((($currentMonthBurned - $previousMonthBurned) / $previousMonthBurned) * 100, 2)
            : 0;

        $netChange = $previousMonthNet != 0
            ? round((($currentMonthNet - $previousMonthNet) / abs($previousMonthNet)) * 100, 2)
            : 0;

        return [
            'current_month' => [
                'intake' => round($currentMonthIntake, 2),
                'burned' => round($currentMonthBurned, 2),
                'net' => round($currentMonthNet, 2)
            ],
            'previous_month' => [
                'intake' => round($previousMonthIntake, 2),
                'burned' => round($previousMonthBurned, 2),
                'net' => round($previousMonthNet, 2)
            ],
            'percentage_changes' => [
                'intake' => $intakeChange,
                'burned' => $burnedChange,
                'net' => $netChange
            ],
            'weekly_breakdown' => $weeklyBreakdown,
            'month_range' => [
                'start' => $startOfMonth->format('Y-m-d'),
                'end' => $endOfMonth->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get last 30 days calories with daily breakdown
     */
    private function getLast30DaysCalories(int $userProfileId): array
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(29); // 30 days including today

        // Total calories for last 30 days
        $totalIntake = $this->getCaloriesIntakeForDateRange($userProfileId, $startDate, $endDate);
        $totalBurned = $this->getCaloriesBurnedForDateRange($userProfileId, $startDate, $endDate);
        $totalNet = $totalIntake - $totalBurned;

        // Daily breakdown
        $last30DaysData = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dayIntake = $this->getCaloriesIntakeForDate($userProfileId, $date);
            $dayBurned = $this->getCaloriesBurnedForDate($userProfileId, $date);

            $last30DaysData[] = [
                'date' => $dateKey,
                'intake' => round($dayIntake, 2),
                'burned' => round($dayBurned, 2),
                'net' => round($dayIntake - $dayBurned, 2),
                'day_name' => $date->format('l')
            ];
        }

        // Calculate averages
        $averageDailyIntake = round($totalIntake / 30, 2);
        $averageDailyBurned = round($totalBurned / 30, 2);
        $averageDailyNet = round($totalNet / 30, 2);

        // Get highest and lowest days
        $intakeValues = collect($last30DaysData)->pluck('intake')->filter(fn($cal) => $cal > 0);
        $burnedValues = collect($last30DaysData)->pluck('burned')->filter(fn($cal) => $cal > 0);

        $highestIntakeDay = collect($last30DaysData)->where('intake', $intakeValues->max())->first();
        $lowestIntakeDay = collect($last30DaysData)->where('intake', $intakeValues->min())->first();
        $highestBurnedDay = collect($last30DaysData)->where('burned', $burnedValues->max())->first();

        return [
            'totals' => [
                'intake' => round($totalIntake, 2),
                'burned' => round($totalBurned, 2),
                'net' => round($totalNet, 2)
            ],
            'averages' => [
                'daily_intake' => $averageDailyIntake,
                'daily_burned' => $averageDailyBurned,
                'daily_net' => $averageDailyNet
            ],
            'daily_data' => $last30DaysData,
            'highlights' => [
                'highest_intake_day' => $highestIntakeDay,
                'lowest_intake_day' => $lowestIntakeDay,
                'highest_burned_day' => $highestBurnedDay
            ],
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get calories intake from food for a specific date
     */
    private function getCaloriesIntakeForDate(int $userProfileId, Carbon $date): float
    {
        $intake = DB::table('food_diaries')
            ->join('food_inputs', 'food_diaries.id', '=', 'food_inputs.food_diaries_id')
            ->join('food_items', 'food_inputs.food_item_id', '=', 'food_items.id')
            ->where('food_diaries.user_profiles_id', $userProfileId)
            ->whereDate('food_diaries.date', $date->format('Y-m-d'))
            ->sum(DB::raw('food_items.calories * food_inputs.portion_size'));

        return $intake ?? 0;
    }

    /**
     * Get calories intake from food for a date range
     */
    private function getCaloriesIntakeForDateRange(int $userProfileId, Carbon $startDate, Carbon $endDate): float
    {
        $intake = DB::table('food_diaries')
            ->join('food_inputs', 'food_diaries.id', '=', 'food_inputs.food_diaries_id')
            ->join('food_items', 'food_inputs.food_item_id', '=', 'food_items.id')
            ->where('food_diaries.user_profiles_id', $userProfileId)
            ->whereBetween('food_diaries.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum(DB::raw('food_items.calories * food_inputs.portion_size'));

        return $intake ?? 0;
    }

    /**
     * Get calories burned from exercise for a specific date
     * Formula: MET × weight (kg) × duration (hours)
     */
    private function getCaloriesBurnedForDate(int $userProfileId, Carbon $date): float
    {
        // Get user weight (assuming it's in user_profiles table)
        $userWeight = DB::table('user_profiles')
            ->where('id', $userProfileId)
            ->value('weight'); // Assuming weight column exists

        if (!$userWeight) {
            $userWeight = 70; // Default weight if not found
        }

        $burned = DB::table('activities')
            ->join('exercises', 'activities.exercise_id', '=', 'exercises.id')
            ->where('activities.user_profiles_id', $userProfileId)
            ->whereDate('activities.date', $date->format('Y-m-d'))
            ->whereNotNull('activities.exercise_id')
            ->whereNotNull('activities.duration')
            ->selectRaw('SUM(exercises.met_value * ' . $userWeight . ' * (activities.duration / 60)) as total_burned')
            ->value('total_burned');

        return $burned ?? 0;
    }

    /**
     * Get calories burned from exercise for a date range
     */
    private function getCaloriesBurnedForDateRange(int $userProfileId, Carbon $startDate, Carbon $endDate): float
    {
        // Get user weight
        $userWeight = DB::table('user_profiles')
            ->where('id', $userProfileId)
            ->value('weight');

        if (!$userWeight) {
            $userWeight = 70; // Default weight
        }

        $burned = DB::table('activities')
            ->join('exercises', 'activities.exercise_id', '=', 'exercises.id')
            ->where('activities.user_profiles_id', $userProfileId)
            ->whereBetween('activities.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotNull('activities.exercise_id')
            ->whereNotNull('activities.duration')
            ->selectRaw('SUM(exercises.met_value * ' . $userWeight . ' * (activities.duration / 60)) as total_burned')
            ->value('total_burned');

        return $burned ?? 0;
    }

    public function getActivityData(Request $request)
    {
        $userProfileId = $request->input('user_profiles_id');

        return response()->json([
            'steps' => [
                'daily' => $this->getDailySteps($userProfileId),
                'weekly' => $this->getWeeklySteps($userProfileId),
                'monthly' => $this->getMonthlySteps($userProfileId),
                'last30Days' => $this->getLast30DaysSteps($userProfileId)
            ]
        ]);
    }
    /**
     * Get daily steps for today
     */
    private function getDailySteps(int $userProfileId): array
    {
        $today = Carbon::today();

        $todaySteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereDate('date', $today)
            ->sum('steps');
        $todaySteps = $todaySteps ?? 0;

        $yesterday = Carbon::yesterday();
        $yesterdaySteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereDate('date', $yesterday)
            ->sum('steps');
        $yesterdaySteps = $yesterdaySteps ?? 0;

        $percentageChange = $yesterdaySteps > 0
            ? round((($todaySteps - $yesterdaySteps) / $yesterdaySteps) * 100, 2)
            : 0;

        return [
            'today' => $todaySteps,
            'yesterday' => $yesterdaySteps,
            'percentage_change' => $percentageChange,
            'date' => $today->format('Y-m-d')
        ];
    }

    /**
     * Get weekly steps (current week vs previous week)
     */
    private function getWeeklySteps(int $userProfileId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Current week steps
        $currentWeekSteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->sum('steps');
        $currentWeekSteps = $currentWeekSteps ?? 0;

        // Previous week steps
        $startOfPrevWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfPrevWeek = Carbon::now()->subWeek()->endOfWeek();

        $previousWeekSteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startOfPrevWeek->format('Y-m-d'), $endOfPrevWeek->format('Y-m-d')])
            ->sum('steps');
        $previousWeekSteps = $previousWeekSteps ?? 0;

        // Daily breakdown for current week
        $dailyBreakdown = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(steps) as total_steps'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(fn($item) => (int) $item->total_steps);

        // Fill missing dates with 0
        $weeklyData = [];
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $weeklyData[$dateKey] = $dailyBreakdown[$dateKey] ?? 0;
        }

        $percentageChange = $previousWeekSteps > 0
            ? round((($currentWeekSteps - $previousWeekSteps) / $previousWeekSteps) * 100, 2)
            : 0;

        return [
            'current_week' => $currentWeekSteps,
            'previous_week' => $previousWeekSteps,
            'percentage_change' => $percentageChange,
            'daily_breakdown' => $weeklyData,
            'week_range' => [
                'start' => $startOfWeek->format('Y-m-d'),
                'end' => $endOfWeek->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get monthly steps (current month vs previous month)
     */
    private function getMonthlySteps(int $userProfileId): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Current month steps
        $currentMonthSteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->sum('steps');
        $currentMonthSteps = $currentMonthSteps ?? 0;

        // Previous month steps
        $startOfPrevMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfPrevMonth = Carbon::now()->subMonth()->endOfMonth();

        $previousMonthSteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startOfPrevMonth->format('Y-m-d'), $endOfPrevMonth->format('Y-m-d')])
            ->sum('steps');
        $previousMonthSteps = $previousMonthSteps ?? 0;

        // Weekly breakdown for current month
        $weeklyBreakdown = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->select(
                DB::raw('YEARWEEK(date, 1) as week'),
                DB::raw('SUM(steps) as total_steps'),
                DB::raw('MIN(date) as week_start'),
                DB::raw('MAX(date) as week_end')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(function ($item) {
                return [
                    'week' => $item->week,
                    'total_steps' => (int) $item->total_steps,
                    'week_start' => $item->week_start,
                    'week_end' => $item->week_end
                ];
            });

        $percentageChange = $previousMonthSteps > 0
            ? round((($currentMonthSteps - $previousMonthSteps) / $previousMonthSteps) * 100, 2)
            : 0;

        return [
            'current_month' => $currentMonthSteps,
            'previous_month' => $previousMonthSteps,
            'percentage_change' => $percentageChange,
            'weekly_breakdown' => $weeklyBreakdown,
            'month_range' => [
                'start' => $startOfMonth->format('Y-m-d'),
                'end' => $endOfMonth->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get last 30 days steps with daily breakdown
     */
    private function getLast30DaysSteps(int $userProfileId): array
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(29); // 30 days including today

        // Total steps for last 30 days
        $totalSteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('steps');
        $totalSteps = $totalSteps ?? 0;

        // Daily breakdown
        $dailySteps = Activity::where('user_profiles_id', $userProfileId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(steps) as total_steps'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(fn($item) => (int) $item->total_steps);

        // Fill missing dates with 0
        $last30DaysData = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $last30DaysData[] = [
                'date' => $dateKey,
                'steps' => $dailySteps[$dateKey] ?? 0,
                'day_name' => $date->format('l')
            ];
        }

        // Calculate averages
        $averageDaily = round($totalSteps / 30, 0);
        $averageWeekly = round($totalSteps / 4.3, 0); // approximately 4.3 weeks in 30 days

        // Get highest and lowest days
        $stepsOnly = collect($last30DaysData)->pluck('steps')->filter(fn($steps) => $steps > 0);
        $highestDay = collect($last30DaysData)->where('steps', $stepsOnly->max())->first();
        $lowestDay = collect($last30DaysData)->where('steps', $stepsOnly->min())->first();

        return [
            'total_steps' => $totalSteps,
            'average_daily' => $averageDaily,
            'average_weekly' => $averageWeekly,
            'daily_data' => $last30DaysData,
            'highest_day' => $highestDay,
            'lowest_day' => $lowestDay,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }
}