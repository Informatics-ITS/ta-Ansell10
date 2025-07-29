<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestMealReminder extends Command
{
    protected $signature = 'test:meal-reminder {user_profile_id} {meal_type}';
    protected $description = 'Test meal reminder notification';

    public function handle(NotificationService $notificationService)
    {
        $userProfileId = $this->argument('user_profile_id');
        $mealType = $this->argument('meal_type');

        $notification = $notificationService->createMealReminder($userProfileId, $mealType);

        if ($notification) {
            $this->info("Test notification created successfully!");
            $this->info("Notification ID: {$notification->id}");
        } else {
            $this->error("Failed to create notification");
        }
    }
}