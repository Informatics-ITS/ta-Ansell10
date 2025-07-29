<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperArticle
 */
class Article extends Model
{
    protected $table = 'articles';
    //
    protected $fillable = [
        'title',
        'summary',
        'content',
        'tag',
        'activity_level',
        'image_url',
        'author',
        'source',
        'created_at',
        'updated_at'
    ];
}
