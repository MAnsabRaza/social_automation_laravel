<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
    protected $fillable = [
        'user_id',
        'group_name',
        'description',
    ];
}
