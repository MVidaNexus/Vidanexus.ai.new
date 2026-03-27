<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolError extends Model
{
    protected $fillable = [
        'user_id',
        'tool_slug',
        'component',
        'error_message',
        'latency_ms',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($toolSlug, $error, $component = null, $userId = null, $payload = [])
    {
        return self::create([
            'user_id' => $userId ?? (auth()->check() ? auth()->id() : null),
            'tool_slug' => $toolSlug,
            'component' => $component,
            'error_message' => $error instanceof \Exception ? $error->getMessage() : $error,
            'payload' => $payload,
        ]);
    }
}
