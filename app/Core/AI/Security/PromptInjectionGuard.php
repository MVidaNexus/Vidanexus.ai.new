<?php

namespace App\Core\AI\Security;

use Illuminate\Support\Facades\Log;

/**
 * PromptInjectionGuard
 *
 * Detects and neutralizes common prompt-injection / jailbreak attempts in
 * user-supplied text before it reaches the LLM.
 *
 * Defense-in-depth strategy:
 *  1. Detect: regex/keyword pass — flag suspicious phrases.
 *  2. Sanitize: strip embedded instruction syntax (system tags, role
 *     markers, fake "developer mode" prefaces).
 *  3. Wrap: even sanitized user content is wrapped in a clear delimiter
 *     so the model never confuses it with its own system instructions.
 *  4. Log: every detection is logged for audit + future blocklist tuning.
 *
 * The guard is intentionally conservative — false positives are a worse
 * UX than letting a marginal phrase through, so we lean toward sanitize
 * over reject. Hard-block only on the highest-signal patterns
 * (e.g. "ignore all previous instructions").
 */
class PromptInjectionGuard
{
    /**
     * Patterns that ALWAYS warrant rejection. Each is a case-insensitive
     * regex matched against the joined input. Keep them tight — broad
     * patterns produce false positives in legitimate research/blog topics.
     *
     * @var array<int, string>
     */
    protected const HARD_BLOCK_PATTERNS = [
        '/ignore\s+(all\s+)?(previous|prior|above|earlier)\s+(instructions?|prompts?|rules?|system\s+messages?)/i',
        '/disregard\s+(all\s+)?(previous|prior|above|earlier)\s+(instructions?|prompts?|rules?)/i',
        '/forget\s+(all\s+)?(previous|prior|earlier)\s+(instructions?|prompts?|rules?)/i',
        '/reveal\s+(your\s+)?(system\s+)?(prompt|instructions?|rules?)/i',
        '/(show|print|display|repeat|output)\s+(your\s+)?(system\s+)?(prompt|instructions?)/i',
        '/(bypass|override|disable|circumvent)\s+(safety|policy|policies|restrictions?|guard\s*rails?|filters?)/i',
        '/(jailbreak|jail\s*break|developer\s*mode|dan\s*mode|godmode|god\s*mode)/i',
        '/(act|pretend|behave|roleplay|role\s*play)\s+as\s+(?:a\s+)?(?:system|admin|root|developer|unrestricted|uncensored|dan)\b/i',
        '/<\s*\/?\s*(system|assistant|developer|user)\s*>/i',
        '/\[\s*(system|assistant|developer)\s*\]/i',
        '/from\s+now\s+on,?\s+you\s+(are|will)\s+/i',
        '/you\s+are\s+now\s+(?:in\s+)?(?:dan|developer|root|admin|unrestricted|uncensored)/i',
    ];

    /**
     * Patterns that should be sanitized (stripped) but not block the
     * request. They tend to appear in legitimate content as well.
     *
     * @var array<int, string>
     */
    protected const SANITIZE_PATTERNS = [
        // Role-play markers commonly used to inject a system message.
        '/^\s*system\s*:/im',
        '/^\s*\[system\]\s*/im',
        '/^\s*\[\/?(system|assistant|developer|user)\]\s*/im',
        '/<\s*\|im_start\|\s*>/i',
        '/<\s*\|im_end\|\s*>/i',
        '/###\s*(system|instructions?)\s*###/i',
    ];

    /**
     * Maximum allowed length for any free-form user field — prevents
     * payload-bombing attacks that try to drown the system prompt.
     */
    public const MAX_FIELD_LENGTH = 2000;

    /**
     * Run a string through detect → sanitize. Returns a struct with
     * `safe` (bool), `cleaned` (string), and `findings` (array of matched
     * patterns).
     *
     * @return array{safe:bool, cleaned:string, findings:array<int, string>}
     */
    public function inspect(string $input, string $context = 'user_input'): array
    {
        $cleaned = $this->truncate($input);
        $findings = [];

        // Hard blocks first — these are not safe to sanitize through.
        foreach (self::HARD_BLOCK_PATTERNS as $pattern) {
            if (preg_match($pattern, $cleaned, $m)) {
                $findings[] = 'block:'.$m[0];
            }
        }

        if (! empty($findings)) {
            Log::channel('audit')->warning('ai.security.prompt_injection_blocked', [
                'context' => $context,
                'findings' => $findings,
                'preview' => mb_substr($cleaned, 0, 240),
            ]);

            return [
                'safe' => false,
                'cleaned' => '',
                'findings' => $findings,
            ];
        }

        // Soft sanitize.
        foreach (self::SANITIZE_PATTERNS as $pattern) {
            $before = $cleaned;
            $cleaned = preg_replace($pattern, '', $cleaned) ?? $cleaned;
            if ($cleaned !== $before) {
                $findings[] = 'sanitize:'.$pattern;
            }
        }

        // Strip zero-width / direction-override unicode that can hide
        // injection text from the human eye.
        $cleaned = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $cleaned) ?? $cleaned;

        if (! empty($findings)) {
            Log::channel('audit')->info('ai.security.prompt_sanitized', [
                'context' => $context,
                'findings' => $findings,
            ]);
        }

        return [
            'safe' => true,
            'cleaned' => trim($cleaned),
            'findings' => $findings,
        ];
    }

    /**
     * Wrap user content inside a clearly-delimited block. The system prompt
     * always lives outside this block so the LLM treats user text as data,
     * not instructions.
     */
    public function wrapAsUserData(string $cleaned, string $label = 'user_content'): string
    {
        $marker = strtoupper($label);
        return "<{$marker}>\n".$cleaned."\n</{$marker}>";
    }

    /**
     * Inspect every field in an associative array. Returns a normalized
     * struct describing what was rejected, what was sanitized, and the
     * cleaned values ready to feed into the prompt builder.
     *
     * @param array<string, mixed> $fields
     * @return array{
     *     safe:bool,
     *     blocked_fields:array<int, string>,
     *     cleaned:array<string, string>,
     *     findings:array<string, array<int, string>>
     * }
     */
    public function inspectFields(array $fields): array
    {
        $cleaned = [];
        $findings = [];
        $blocked = [];

        foreach ($fields as $key => $value) {
            if (! is_string($value) || $value === '') {
                $cleaned[$key] = (string) $value;
                continue;
            }

            $result = $this->inspect($value, "field:{$key}");
            $findings[$key] = $result['findings'];

            if (! $result['safe']) {
                $blocked[] = $key;
                continue;
            }

            $cleaned[$key] = $result['cleaned'];
        }

        return [
            'safe' => empty($blocked),
            'blocked_fields' => $blocked,
            'cleaned' => $cleaned,
            'findings' => $findings,
        ];
    }

    protected function truncate(string $input): string
    {
        if (mb_strlen($input) <= self::MAX_FIELD_LENGTH) {
            return $input;
        }
        return mb_substr($input, 0, self::MAX_FIELD_LENGTH);
    }
}
