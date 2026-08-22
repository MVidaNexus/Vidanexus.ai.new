<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standard JSON envelope for API routes.
 */
final class ApiResponse
{
    public static function ok(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, ?string $code = null, mixed $details = null): JsonResponse
    {
        $body = [
            'ok' => false,
            'message' => $message,
        ];
        if ($code !== null) {
            $body['code'] = $code;
        }
        if ($details !== null) {
            $body['details'] = $details;
        }

        return response()->json($body, $status);
    }
}
