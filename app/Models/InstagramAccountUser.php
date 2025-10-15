<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Instagram Account User Pivot Model
 * 
 * Custom pivot model to handle type casting for the instagram_account_user table.
 */
class InstagramAccountUser extends Pivot
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'can_post' => 'boolean',
        'can_manage' => 'boolean',
        'shared_at' => 'datetime',
    ];
}

