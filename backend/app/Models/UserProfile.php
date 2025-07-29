<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperUserProfile
 */
class UserProfile extends Model
{
    use HasFactory;
    protected $table = 'user_profiles'; // Ini yang benar
    protected $fillable = [
        'user_id',
        'name',
        'weight',
        'height',
        'age',
        'gender',
        'activity_level',
        'bmr',
        'tdee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foodDiaries()
    {
        return $this->hasMany(FoodDiary::class, 'user_profiles_id');
    }

    // NEW: Notification relationships
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_profiles_id');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_profiles_id')
                    ->where('is_read', false)
                    ->orderBy('created_at', 'desc');
    }

    public function recentNotifications($limit = 10)
    {
        return $this->hasMany(Notification::class, 'user_profiles_id')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
    }

    // Helper methods for notifications
    public function getUnreadNotificationCountAttribute()
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function hasUnreadNotifications()
    {
        return $this->unreadNotifications()->exists();
    }

    // Check if user had specific meal today
    public function hadMealToday($mealType, $date = null)
    {
        $date = $date ?? Carbon::today()->toDateString();

        return $this->foodDiaries()
                    ->where('date', $date)
                    ->where('meal_type', $mealType)
                    ->exists();
    }


    // Get last meal time (useful for intermittent fasting)
    public function getLastMealTime($daysBack = 2)
    {
        $startDate = Carbon::now()->subDays($daysBack)->toDateString();

        return $this->foodDiaries()
                    ->where('date', '>=', $startDate)
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    // Check if notification was already sent today for specific meal
    public function hasReminderBeenSentToday($mealType, $notificationType = 'meal_reminder', $date = null)
    {
        $date = $date ?? Carbon::today()->toDateString();

        return $this->notifications()
                    ->whereIn('type', [$notificationType, 'intermittent_fasting'])
                    ->whereJsonContains('data->meal_type', $mealType)
                    ->whereDate('created_at', $date)
                    ->where('status', 'sent')
                    ->exists();
    }

    // Get hours since last meal (for intermittent fasting)
    public function getHoursSinceLastMeal()
    {
        $lastMeal = $this->getLastMealTime();

        if (!$lastMeal) {
            return 24; // Default to 24 hours if no meal found
        }

        return Carbon::now()->diffInHours($lastMeal->created_at);
    }

    // Mark all notifications as read
    public function markAllNotificationsAsRead()
    {
        return $this->notifications()
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
    }

    // Get meal history for specific date range
    public function getMealHistory($startDate = null, $endDate = null, $mealType = null)
    {
        $startDate = $startDate ?? Carbon::now()->subDays(7)->toDateString();
        $endDate = $endDate ?? Carbon::today()->toDateString();

        $query = $this->foodDiaries()
                      ->whereBetween('date', [$startDate, $endDate])
                      ->orderBy('date', 'desc')
                      ->orderBy('created_at', 'desc');

        if ($mealType) {
            $query->where('meal_type', $mealType);
        }

        return $query->get();
    }

    // Check if user is likely doing intermittent fasting
    public function isLikelyIntermittentFasting($daysToCheck = 7)
    {
        $startDate = Carbon::now()->subDays($daysToCheck)->toDateString();

        // Count days with breakfast in the last week
        $breakfastDays = $this->foodDiaries()
                              ->where('date', '>=', $startDate)
                              ->where('meal_type', 'breakfast')
                              ->distinct('date')
                              ->count();

        // If less than 30% of days have breakfast, likely doing IF
        return $breakfastDays < ($daysToCheck * 0.3);
    }

    // Get notification preferences (you can expand this later)
    public function getNotificationPreferences()
    {
        // For now, return default preferences
        // Later you can create a separate notification_preferences table
        return [
            'breakfast_reminder' => true,
            'lunch_reminder' => true,
            'dinner_reminder' => true,
            'intermittent_fasting' => true,
            'reminder_frequency' => 'daily',
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '07:00'
        ];
    }

    // Create a meal reminder notification
    public function createMealReminder($mealType, $customMessage = null)
    {
        $messages = [
            'breakfast' => $customMessage ?? "Good morning {$this->name}! Don't forget to log your breakfast.",
            'lunch' => $customMessage ?? "Hi {$this->name}! Time for lunch - fuel your afternoon.",
            'dinner' => $customMessage ?? "Evening {$this->name}! Remember to have a balanced dinner.",
            'snack' => $customMessage ?? "Hey {$this->name}! A healthy snack can boost your energy."
        ];

        $titles = [
            'breakfast' => 'Breakfast Reminder',
            'lunch' => 'Lunch Reminder',
            'dinner' => 'Dinner Reminder',
            'snack' => 'Snack Reminder'
        ];

        return $this->notifications()->create([
            'type' => 'meal_reminder',
            'title' => $titles[$mealType] ?? 'Meal Reminder',
            'message' => $messages[$mealType] ?? 'Time for your meal!',
            'data' => [
                'meal_type' => $mealType,
                'created_by' => 'system',
                'reminder_time' => Carbon::now()->toISOString(),
                'profile_name' => $this->name
            ],
            'scheduled_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'status' => 'sent'
        ]);
    }

    // Create intermittent fasting notification
    public function createIntermittentFastingNotification($fastingHours, $lastMealTime = null)
    {
        $encouragementMessages = [
            12 => "Great job {$this->name}! You've completed 12 hours of fasting. 🎉",
            16 => "Amazing {$this->name}! 16 hours of intermittent fasting completed. Your body is in fat-burning mode! 🔥",
            18 => "Incredible {$this->name}! 18 hours of fasting - you're doing fantastic! 💪",
            20 => "Outstanding {$this->name}! 20+ hours of fasting. Consider breaking your fast when ready. 🌟"
        ];

        $message = $encouragementMessages[20]; // Default for 20+ hours
        foreach ($encouragementMessages as $hours => $msg) {
            if ($fastingHours >= $hours) {
                $message = $msg;
            }
        }

        return $this->notifications()->create([
            'type' => 'intermittent_fasting',
            'title' => 'Intermittent Fasting Achievement',
            'message' => $message,
            'data' => [
                'meal_type' => 'breakfast', // Usually IF notifications relate to skipped breakfast
                'fasting_hours' => $fastingHours,
                'last_meal_time' => $lastMealTime,
                'achievement_level' => $this->getFastingLevel($fastingHours),
                'suggestions' => $this->getFastingSuggestions($fastingHours),
                'profile_name' => $this->name
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

    // Override boot method to add model events (optional)
    protected static function boot()
    {
        parent::boot();

        // Clean up notifications when profile is deleted
        static::deleting(function ($profile) {
            $profile->notifications()->delete();
        });

        // You can add other model events here
        static::created(function ($profile) {
            // Send welcome notification when profile is created
            $profile->createWelcomeNotification();
        });
    }

    // Welcome notification for new profiles
    public function createWelcomeNotification()
    {
        return $this->notifications()->create([
            'type' => 'general',
            'title' => 'Welcome to Your Health Journey!',
            'message' => "Hi {$this->name}! Welcome to your personalized nutrition tracking. We'll help you stay on track with your meals and health goals.",
            'data' => [
                'notification_type' => 'welcome',
                'profile_created' => true,
                'profile_name' => $this->name
            ],
            'scheduled_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
            'status' => 'sent'
        ]);
    }
}