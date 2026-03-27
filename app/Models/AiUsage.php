<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    protected $table = 'ai_usages';

    protected $fillable = [
        'user_id',
        'tool',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'latency_ms',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
