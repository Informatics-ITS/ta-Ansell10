<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            // Get user profiles for this user
            $userProfileIds = UserProfile::where('user_id', $user->id)->pluck('id');

            $limit = $request->get('limit', 20);
            $includeRead = $request->boolean('include_read', true);

            $query = Notification::whereIn('user_profiles_id', $userProfileIds)
                ->orderBy('created_at', 'desc');

            if (!$includeRead) {
                $query->unread();
            }

            $notifications = $query->limit($limit)->get();

            $unreadCount = Notification::whereIn('user_profiles_id', $userProfileIds)
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request, $id)
    {
        try {
            $user = auth()->user();

            // Get user profiles for this user
            $userProfileIds = UserProfile::where('user_id', $user->id)->pluck('id');

            $notification = Notification::whereIn('user_profiles_id', $userProfileIds)
                ->where('id', $id)
                ->firstOrFail();

            $notification->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $user = auth()->user();

            // Get user profiles for this user
            $userProfileIds = UserProfile::where('user_id', $user->id)->pluck('id');

            Notification::whereIn('user_profiles_id', $userProfileIds)
                ->unread()
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}