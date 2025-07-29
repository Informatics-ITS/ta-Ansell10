<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperExercise
 */
class Exercise extends Model
{
    protected $fillable = [
        'name',
        'category',
        'met_value',
        'description',
    ];
}
