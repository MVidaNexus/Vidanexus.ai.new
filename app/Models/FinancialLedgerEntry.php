<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Unified append-only ledger for credits, tool bonus pool changes, and related financial events.
 */
class FinancialLedgerEntry extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'wallet_delta',
        'bonus_delta',
        'tool_slug',
        'reference',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
