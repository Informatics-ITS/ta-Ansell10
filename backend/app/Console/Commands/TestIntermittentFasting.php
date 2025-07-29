<?php

namespace App\Console\Commands;

use App\Models\FoodDiary;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestIntermittentFasting extends Command
{
    protected $signature = 'test:if {user_id} {user_profile_id} {--hours=16} {--create-meal} {--clear-notifications}';
    protected $description = 'Test intermittent fasting notification system';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $userProfileId = $this->argument('user_profile_id');
        $fastingHours = $this->option('hours');
        $createMeal = $this->option('create-meal');
        $clearNotifications = $this->option('clear-notifications');

        $profile = UserProfile::find($userProfileId);
        if (!$profile) {
            $this->error("UserProfile with ID {$userProfileId} not found!");
            return 1;
        }

        $this->info("Testing Intermittent Fasting for: {$profile->name}");
        $this->info("Fasting Hours: {$fastingHours}");

        // Clear existing notifications if requested
        if ($clearNotifications) {
            $deleted = $profile->notifications()->delete();
            $this->info("Cleared {$deleted} existing notifications");
        }

        // Create a fake last meal from X hours ago
        if ($createMeal) {
            $lastMealTime = Carbon::now()->subHours($fastingHours);

            // Delete any existing meals for the test
            FoodDiary::where('user_profiles_id', $userProfileId)
                     ->where('created_at', '>=', $lastMealTime->subHours(1))
                     ->delete();

            $lastMeal = FoodDiary::create([
                'user_id' => $userId,
                'user_profiles_id' => $userProfileId,
                'date' => $lastMealTime->toDateString(),
                'meal_type' => 'dinner',
                'notes' => 'Test meal for IF testing',
                'created_at' => $lastMealTime,
                'updated_at' => $lastMealTime
            ]);

            $this->info("Created test meal at: {$lastMealTime->format('Y-m-d H:i:s')}");
        }

        // Get current status
        $hoursSinceLastMeal = $profile->getHoursSinceLastMeal();
        $this->info("Hours since last meal: {$hoursSinceLastMeal}");

        // Test the notification creation
        if ($hoursSinceLastMeal >= 12) {
            $notification = $profile->createIntermittentFastingNotification($hoursSinceLastMeal);

            $this->info("✅ Intermittent Fasting notification created!");
            $this->info("Notification ID: {$notification->id}");
            $this->info("Title: {$notification->title}");
            $this->info("Message: {$notification->message}");

            $data = $notification->data;
            $this->info("Achievement Level: {$data['achievement_level']}");
            $this->info("Suggestions: " . implode(', ', $data['suggestions']));
        } else {
            $this->warn("Not enough fasting hours for IF notification ({$hoursSinceLastMeal}h < 12h)");

            // Create regular breakfast reminder instead
            $notification = $profile->createMealReminder('breakfast');
            $this->info("✅ Regular breakfast reminder created instead");
            $this->info("Notification ID: {$notification->id}");
        }

        // Show current notifications
        $this->info("\n--- Current Notifications for {$profile->name} ---");
        $notifications = $profile->notifications()->latest()->limit(5)->get();

        foreach ($notifications as $notif) {
            $this->line("#{$notif->id} - {$notif->type} - {$notif->title} - {$notif->created_at->diffForHumans()}");
        }

        return 0;
    }
}