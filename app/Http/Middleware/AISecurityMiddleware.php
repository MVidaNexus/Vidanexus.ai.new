<?php

namespace App\Http\Middleware;

use App\Core\AI\Security\PromptInjectionGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AISecurityMiddleware
 *
 * Sits in front of any controller that forwards user input to an LLM. It
 * runs every flagged free-text field through {@see PromptInjectionGuard}
 * and either:
 *   - Rejects the request with a structured 422 envelope when a hard-block
 *     pattern is detected (e.g. "ignore previous instructions").
 *   - Or rewrites the request payload in place with sanitized values, so
 *     the controller never sees raw injection markers.
 *
 * The set of fields to inspect is defined by AI_GUARDED_FIELDS — anything
 * not in that list passes through untouched (we can't blanket-scan because
 * many tools accept legitimate text like JSON snippets).
 */
class AISecurityMiddleware
{
    /**
     * Free-text fields that get inspected on every AI tool request.
     */
    public const GUARDED_FIELDS = [
        'keyword',
        'topic',
        'tone',
        'audience',
        'language',
        'additional_instructions',
        'instructions',
        'description',
        'context',
    ];

    public function __construct(protected PromptInjectionGuard $guard) {}

    public function handle(Request $request, Closure $next): Response
    {
        $payload = [];
        foreach (self::GUARDED_FIELDS as $field) {
            if ($request->has($field)) {
                $payload[$field] = $request->input($field);
            }
        }

        if ($payload === []) {
            return $next($request);
        }

        $report = $this->guard->inspectFields($payload);

        if (! $report['safe']) {
            return response()->json([
                'status' => 'error',
                'code' => 'AI_PROMPT_INJECTION_BLOCKED',
                'message' => 'Your request contains content that may be a prompt-injection attempt and was blocked.',
                'blocked_fields' => $report['blocked_fields'],
            ], 422);
        }

        // Replace the inspected fields with their sanitized versions.
        $request->merge($report['cleaned']);

        return $next($request);
    }
}
