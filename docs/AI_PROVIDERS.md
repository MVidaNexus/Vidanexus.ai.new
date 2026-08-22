# AI Provider Architecture

VidaNexus AI's tools are dispatched through a single `App\Core\AI\AIManager`. The manager owns:

- A registry of `AIProvider` implementations (OpenAI, Google Gemini, OpenRouter, …).
- A configurable fallback chain.
- Provider-level configuration validation (no API key → skip cleanly, never crash).
- Standardized response envelope (`provider_used`, `model_used`, `fallback_applied`, `attempts`).
- Standardized error envelope via `App\Http\Responses\AIResponse` and `AIProvider*Exception` classes.

## Request lifecycle

```
ToolController
   │
   │  AISecurityMiddleware  (sanitizes / blocks prompt-injection patterns)
   │
   ▼
ToolService::generate(...)
   │
   │  PromptInjectionGuard  (defense-in-depth re-sanitize)
   │  Builds final prompt + system_prompt
   │
   ▼
AIManager::generate('tool-slug', $prompt, [...])
   │
   │  Resolves chain (per-tool override → global default)
   │  For each link:
   │    • normalizeModel()
   │    • resolveApiKey()
   │    • provider->isConfigured() → skip if false
   │    • provider->generate()  → success || retry next
   │
   ▼
{
  "text": "...",
  "input_tokens": 123,
  "output_tokens": 456,
  "provider_used": "google",
  "model_used": "gemini-1.5-flash",
  "fallback_applied": true,
  "attempts": [
    { "provider": "openai", "model": "gpt-4o-mini", "error": "OpenAI API failed (401): ..." }
  ]
}
```

## Configuring providers

API keys come from (in order):

1. Per-call `options['api_key']` override.
2. Per-tool `Setting('{tool}_ai_chain')[i].api_key` override.
3. Database `Setting('{provider}_api_key')`.
4. Environment variable (`OPENAI_API_KEY`, `GEMINI_API_KEY`, `OPENROUTER_API_KEY`).

Example tool-specific chain stored as a Setting (JSON):

```json
[
  { "provider": "openai", "model": "gpt-4o-mini" },
  { "provider": "google", "model": "gemini-1.5-flash" },
  { "provider": "openrouter", "model": "google/gemini-2.0-flash-001" }
]
```

The manager skips entries whose API key isn't configured (logged as `ai.provider_skipped` for ops visibility).

## Health check

`AIManager::healthCheck()` returns a per-provider configuration map with no network calls. Suitable to expose at an admin dashboard:

```php
$report = app(AIManager::class)->healthCheck();
// [
//   'openai'     => ['configured' => true,  'has_api_key' => true],
//   'google'     => ['configured' => false, 'has_api_key' => false],
//   'openrouter' => ['configured' => true,  'has_api_key' => true],
// ]
```

## Fallback chain

The default global chain is defined in `config/vidanexus.php`:

```php
'ai' => [
    'failover_order' => ['openai', 'google', 'openrouter'],
],
```

`AIManager::resolveChain()` builds the effective chain for every call as:

1. The per-tool chain stored in `Setting('{tool}_ai_chain')` (admin preference, may pin a model and api_key per entry).
2. The global `failover_order`, **appended** after the per-tool preferences with any duplicates removed.

This means a tool that prefers OpenAI still falls back to Google and OpenRouter when OpenAI is down or unconfigured — without having to repeat the global providers in every per-tool chain.

### Strict mode (opt-out)

If an admin truly wants a tool to be limited to the per-tool chain only (e.g. a tool that may only use Gemini for compliance reasons), set:

```php
Setting::set("{$tool}_ai_chain_strict", true, 'boolean', 'tool_settings');
```

…or pass `options['strict_chain' => true]` at the call site. In strict mode the global chain is not appended.

### What happens on failure

Each failure is captured and:

1. Logged as `ai.failover` with the provider, model, and error.
2. Pushed to the `attempts` array of the final response.
3. Followed by a retry on the next provider in the resolved chain.

When the chain is exhausted, `App\Core\AI\Exceptions\AIProviderFailureException` is thrown carrying the full attempt list. Controllers convert this to a structured 503 via `AIResponse::fromException($e)` — the `details.attempts` field exposes every provider that was tried plus the last error string for each, so frontends and admins can see exactly why generation failed.

## Error envelope

Every AI tool now returns the same envelope:

| Code | HTTP | Meaning |
|------|------|---------|
| `AI_PROVIDER_NOT_CONFIGURED` | 503 | No provider in the chain has a valid API key. |
| `AI_PROVIDER_FAILURE` | 503 | Every provider tried and failed (network, 5xx, model not found, safety filter). |
| `AI_PROMPT_INJECTION_BLOCKED` | 422 | User input matched a hard-block pattern (see SECURITY.md). |
| `INSUFFICIENT_CREDITS` | 402 | User does not own the tool or wallet balance is too low. |
| `AI_UNKNOWN_ERROR` | 500 | Last-resort catch-all (should be rare; investigate when seen). |

Successful responses always include:

```json
{
  "status": "success",
  "provider_used": "google",
  "model_used": "gemini-1.5-flash",
  "fallback_applied": true,
  ...
}
```

## Gemini model resolution

`App\Core\AI\Providers\GoogleProvider::resolveModel()` accepts:

- Native names: `gemini-1.5-flash`, `gemini-2.0-flash`, etc.
- OpenRouter-style names: `google/gemini-2.0-flash-001` → `gemini-2.0-flash`.
- Versioned aliases: `gemini-2.0-flash-001` → `gemini-2.0-flash`.
- Anything unrecognized falls back to `gemini-1.5-flash` so a typo never returns the cryptic "No endpoints found" error.

## Logging

All AI activity is logged to the default channel:

| Event | Level | Notes |
|-------|-------|-------|
| `ai.attempt` | info | Provider call started. |
| `ai.success` | info | Includes `latency_ms`, tokens, fallback flag. |
| `ai.failover` | warning | Provider failed, advancing to next. |
| `ai.config_error` | warning | Provider not configured, skipped. |
| `ai.all_failed` | error | Chain exhausted, throwing `AIProviderFailureException`. |
| `ai.provider_skipped` | info | Provider skipped (no API key) without consuming a slot. |
