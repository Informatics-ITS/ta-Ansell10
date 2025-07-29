<?php

namespace App\Console\Commands;

use App\Models\FoodDiary;
use App\Models\Notification;
use App\Models\UserProfile;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class SendMealReminders extends Command
{
    protected $signature = 'meal:remind';
    protected $description = 'Send meal reminder notifications to users';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $this->info("Starting meal reminder check at {$now->format('Y-m-d H:i:s')}");

        // Get all active user profiles
        $userProfiles = UserProfile::get();

        $remindersSent = 0;

        foreach ($userProfiles as $profile) {
            if ($this->checkBreakfastReminder($profile, $now, $today)) $remindersSent++;
            if ($this->checkLunchReminder($profile, $now, $today)) $remindersSent++;
            if ($this->checkDinnerReminder($profile, $now, $today)) $remindersSent++;
        }

        $this->info("Meal reminder check completed. {$remindersSent} reminders sent.");
        return 0;
    }

    private function checkBreakfastReminder($profile, $now, $today)
    {
        // Check if it's past 11 AM (7 AM + 4 hours) but before 2 PM
        if ($now->hour < 11 || $now->hour >= 14) return false;

        // Check if user already had breakfast today
        $hasBreakfast = FoodDiary::where('user_profiles_id', $profile->id)
            ->where('date', $today)
            ->where('meal_type', 'breakfast')
            ->exists();

        if ($hasBreakfast) return false;

        // Check if we already sent a reminder today
        if ($this->hasReminderBeenSent($profile->id, 'breakfast', $today)) return false;

        // Check for intermittent fasting (12+ hours since last meal)
        $lastMeal = FoodDiary::where('user_profiles_id', $profile->id)
            ->where('date', '>=', Carbon::yesterday()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        $hoursSinceLastMeal = $lastMeal
            ? $now->diffInHours($lastMeal->created_at)
            : 24;

        if ($hoursSinceLastMeal >= 12) {
            // Send intermittent fasting notification
            $this->notificationService->createIntermittentFastingNotification(
                $profile->id,
                $hoursSinceLastMeal,
                $lastMeal?->created_at
            );
            $this->info("IF notification sent to {$profile->name} ({$hoursSinceLastMeal}h fasting)");
        } else {
            // Send regular breakfast reminder
            $this->notificationService->createMealReminder($profile->id, 'breakfast');
            $this->info("Breakfast reminder sent to {$profile->name}");
        }

        return true;
    }

    private function checkLunchReminder($profile, $now, $today)
    {
        if ($now->hour < 14 || $now->hour >= 17) return false;

        $hasLunch = FoodDiary::where('user_profiles_id', $profile->id)
            ->where('date', $today)
            ->where('meal_type', 'lunch')
            ->exists();

        if ($hasLunch) return false;
        if ($this->hasReminderBeenSent($profile->id, 'lunch', $today)) return false;

        $this->notificationService->createMealReminder($profile->id, 'lunch');
        $this->info("Lunch reminder sent to {$profile->name}");
        return true;
    }

    private function checkDinnerReminder($profile, $now, $today)
    {
        if ($now->hour < 20 || $now->hour >= 22) return false;

        $hasDinner = FoodDiary::where('user_profiles_id', $profile->id)
            ->where('date', $today)
            ->where('meal_type', 'dinner')
            ->exists();

        if ($hasDinner) return false;
        if ($this->hasReminderBeenSent($profile->id, 'dinner', $today)) return false;

        $this->notificationService->createMealReminder($profile->id, 'dinner');
        $this->info("Dinner reminder sent to {$profile->name}");
        return true;
    }

    private function hasReminderBeenSent($userProfileId, $mealType, $date)
    {
        return \App\Models\Notification::where('user_profiles_id', $userProfileId)
            ->whereIn('type', ['meal_reminder', 'intermittent_fasting'])
            ->whereJsonContains('data->meal_type', $mealType)
            ->whereDate('created_at', $date)
            ->where('status', 'sent')
            ->exists();
    }
}