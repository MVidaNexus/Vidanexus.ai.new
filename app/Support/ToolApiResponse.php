<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ToolApiResponse
{
    public const AUTH_REQUIRED = 'AUTH_REQUIRED';
    public const TOOL_LOCKED = 'TOOL_LOCKED';
    public const INSUFFICIENT_CREDITS = 'INSUFFICIENT_CREDITS';
    public const FETCH_FAILED = 'FETCH_FAILED';
    public const ALREADY_PROCESSING = 'ALREADY_PROCESSING';
    public const NETWORK_ERROR = 'NETWORK_ERROR';
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const SERVER_ERROR = 'SERVER_ERROR';

    public static function userMessage(string $code, ?string $fallback = null): string
    {
        return match ($code) {
            self::INSUFFICIENT_CREDITS => 'Insufficient credits. Please purchase more.',
            self::FETCH_FAILED => 'Unable to fetch fresh data. Service may be down.',
            self::ALREADY_PROCESSING => 'Refresh in progress. Please wait.',
            self::NETWORK_ERROR => 'Connection timeout. Please try again.',
            self::VALIDATION_ERROR => 'Invalid request data.',
            self::AUTH_REQUIRED => 'Please log in to continue.',
            self::TOOL_LOCKED => 'You need to unlock this tool first.',
            self::SERVER_ERROR => 'Something went wrong on our side. Please try again.',
            default => $fallback ?? 'Something went wrong. Please try again.',
        };
    }

    public static function error(
        string $code,
        string $message,
        int $status = 400,
        array $extra = []
    ): JsonResponse {
        return response()->json(array_merge([
            'success' => false,
            'error_code' => $code,
            'message' => $message,
        ], $extra), $status);
    }
}
