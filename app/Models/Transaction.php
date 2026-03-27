<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'wallet_id', 'type', 'amount', 'tool_name', 'provider_cost_usd', 'reference_id', 'idempotency_key',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
