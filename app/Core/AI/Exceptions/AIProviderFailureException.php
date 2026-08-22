<?php

namespace App\Core\AI\Exceptions;

/**
 * Thrown when the entire fallback chain has been exhausted and no provider
 * could fulfill the request. Always carries the per-provider failure list
 * for observability.
 */
class AIProviderFailureException extends \RuntimeException
{
    /**
     * @param array<int, array{provider:string, model:?string, error:string}> $attempts
     */
    public function __construct(
        string $message,
        public readonly array $attempts = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
