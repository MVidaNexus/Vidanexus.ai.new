<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitlistSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'email',
        'ip_address',
        'user_agent',
        'referral_source',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}
