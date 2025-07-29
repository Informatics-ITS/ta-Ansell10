<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperActivity
 */
class Activity extends Model
{
    //
    protected $fillable = [
        'user_id',
        'user_profiles_id',
        'date',
        'activity',
        'detail',
        'sleep',
        'exercise_id',
        'duration',
        'steps',
        'water_intake',
        'calorie_intake',
    ];
    
}
