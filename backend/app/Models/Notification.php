<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_profiles_id',
        'type',
        'title',
        'message',
        'data',
        'scheduled_at',
        'sent_at',
        'is_read',
        'status'
    ];

    protected $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class, 'user_profiles_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeForUser($query, $userProfileId)
    {
        return $query->where('user_profiles_id', $userProfileId);
    }
}