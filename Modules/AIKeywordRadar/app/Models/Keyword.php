<?php

namespace Modules\AIKeywordRadar\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword',
        'category',
        'lang',
        'source',
        'headline_title',
        'user_id',
        'assigned_admin_id',
        'visibility',
        'allowed_roles',
        'allowed_admins',
        'synced_at',
        'published_at',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'allowed_admins' => 'array',
        'synced_at' => 'datetime',
        'published_at' => 'datetime',
    ];
}
