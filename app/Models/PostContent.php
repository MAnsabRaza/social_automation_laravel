<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostContent extends Model
{
    protected $table = 'post_content';
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'hashtags',
        'spintax_enabled',
        'media_urls',
        'category',
    ];
}
