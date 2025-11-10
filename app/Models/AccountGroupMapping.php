<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroupMapping extends Model
{
    protected $table = 'account_group_mapping';

    protected $fillable = [
        'user_id',
        'account_id',
        'group_id',
    ];
}
