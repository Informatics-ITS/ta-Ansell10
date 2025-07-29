<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\UserProfile;
use App\Models\FoodDiary;
use Carbon\Carbon;

class NotificationService
{
    public function createMealReminder($userProfileId, $mealType, $customMessage = null)
    {
        $profile = UserProfile::find($userProfileId);
        if (!$profile) return false;

        $messages = [
            'breakfast' => $customMessage ?? "Good morning {$profile->name}! Don't forget to log your breakfast for a healthy start.",
            'lunch' => $customMessage ?? "Hi {$profile->name}! Time for lunch - fuel your afternoon with nutritious food.",
            'dinner' => $customMessage ?? "Evening {$profile->name}! Remember to have a balanced dinner.",
            'snack' => $customMessage ?? "Hey {$profile->name}! A healthy snack can boost your energy."
        ];

        $titles = [
            'breakfast' => 'Breakfast Reminder',
            'lunch' => 'Lunch Reminder',
            'dinner' => 'Dinner Reminder',
            'snack' => 'Snack Reminder'
        ];

        return Notification::create([
            'user_profiles_id' => $userProfileId,
            'type' => 'meal_reminder',
            'title' => $titles[$mealType] ?? 'Meal Reminder',
            'message' => $messages[$mealType] ?? 'Time for your meal!',
            'data' => [
                'meal_type' => $mealType,
                'created_by' => 'system',
                'reminder_time' => Carbon::now()->toISOString()
            ],
            'scheduled_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'status' => 'sent'
        ]);
    }

    public function createIntermittentFastingNotification($userProfileId, $fastingHours, $lastMealTime = null)
    {
        $profile = UserProfile::find($userProfileId);
        if (!$profile) return false;

        $encouragementMessages = [
            12 => "Great job {$profile->name}! You've completed 12 hours of fasting. 🎉",
            16 => "Amazing {$profile->name}! 16 hours of intermittent fasting completed. Your body is in fat-burning mode! 🔥",
            18 => "Incredible {$profile->name}! 18 hours of fasting - you're doing fantastic! 💪",
            20 => "Outstanding {$profile->name}! 20+ hours of fasting. Consider breaking your fast when you feel ready. 🌟"
        ];

        $message = $encouragementMessages[20]; // Default for 20+ hours
        foreach ($encouragementMessages as $hours => $msg) {
            if ($fastingHours >= $hours) {
                $message = $msg;
            }
        }

        return Notification::create([
            'user_profiles_id' => $userProfileId,
            'type' => 'intermittent_fasting',
            'title' => 'Intermittent Fasting Achievement',
            'message' => $message,
            'data' => [
                'fasting_hours' => $fastingHours,
                'last_meal_time' => $lastMealTime,
                'achievement_level' => $this->getFastingLevel($fastingHours),
                'suggestions' => $this->getFastingSuggestions($fastingHours)
            ],
            'scheduled_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'status' => 'sent'
        ]);
    }

    private function getFastingLevel($hours)
    {
        if ($hours >= 20) return 'expert';
        if ($hours >= 16) return 'advanced';
        if ($hours >= 12) return 'intermediate';
        return 'beginner';
    }

    private function getFastingSuggestions($hours)
    {
        $suggestions = [];

        if ($hours >= 12 && $hours < 16) {
            $suggestions[] = 'Consider extending to 16 hours for enhanced benefits';
            $suggestions[] = 'Stay hydrated with water, herbal tea, or black coffee';
        } elseif ($hours >= 16 && $hours < 20) {
            $suggestions[] = 'Excellent! You\'re in the optimal fat-burning zone';
            $suggestions[] = 'Break your fast with a protein-rich meal';
        } elseif ($hours >= 20) {
            $suggestions[] = 'Consider breaking your fast to maintain healthy patterns';
            $suggestions[] = 'Ensure you get adequate nutrition when you eat';
        }

        return $suggestions;
    }

    public function getUnreadCount($userProfileId)
    {
        return Notification::where('user_profiles_id', $userProfileId)
            ->unread()
            ->count();
    }

    public function getUserNotifications($userProfileId, $limit = 20, $includeRead = true)
    {
        $query = Notification::where('user_profiles_id', $userProfileId)
            ->orderBy('created_at', 'desc');

        if (!$includeRead) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }
}