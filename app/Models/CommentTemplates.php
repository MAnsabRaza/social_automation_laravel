<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentTemplates extends Model
{
    protected $table = 'comment_templates';
    protected $fillable = [
        'user_id',
        'template_text',
        'spintax_enabled',
        'category',
    ];
}
