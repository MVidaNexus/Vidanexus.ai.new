<?php

namespace App\Http\Responses;

use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Standard JSON envelope for AI tool endpoints.
 *
 * Produces stable, machine-readable error codes so the frontend can react
 * (show retry buttons, surface configuration warnings to admins) without
 * parsing free-form messages.
 *
 * Codes:
 *   AI_PROVIDER_NOT_CONFIGURED  Configuration error — missing API key.
 *   AI_PROVIDER_FAILURE         All providers in the chain failed at runtime.
 *   AI_PROMPT_INJECTION_BLOCKED User input contained an injection attempt.
 *   AI_VALIDATION_ERROR         Input validation rejected the request.
 *   AI_UNKNOWN_ERROR            Unexpected exception (last-resort fallback).
 */
final class AIResponse
{
    public static function success(array $payload, int $status = 200): JsonResponse
    {
        return response()->json(array_merge([
            'status' => 'success',
        ], $payload), $status);
    }

    public static function error(string $code, string $message, int $status = 500, array $details = []): JsonResponse
    {
        $body = [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ];
        if ($details !== []) {
            $body['details'] = $details;
        }
        return response()->json($body, $status);
    }

    /**
     * Translate any exception thrown by the AI stack into a structured
     * response. Callers that catch Throwable can pipe directly through this.
     */
    public static function fromException(Throwable $e): JsonResponse
    {
        if ($e instanceof AIProviderConfigurationException) {
            return self::error(
                'AI_PROVIDER_NOT_CONFIGURED',
                'AI provider is not configured. Please contact support.',
                503,
                ['provider_message' => $e->getMessage()]
            );
        }

        if ($e instanceof AIProviderFailureException) {
            return self::error(
                'AI_PROVIDER_FAILURE',
                'AI provider is currently unavailable. Please try again later.',
                503,
                ['attempts' => $e->attempts]
            );
        }

        return self::error(
            'AI_UNKNOWN_ERROR',
            $e->getMessage() ?: 'An unexpected error occurred. Please try again.',
            500
        );
    }
}
