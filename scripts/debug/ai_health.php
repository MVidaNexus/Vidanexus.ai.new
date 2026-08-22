<?php

/**
 * Bare-PHP AI provider health probe.
 *
 * Designed to run WITHOUT Laravel booted (vendor/autoload.php is not
 * required), so you can answer "which AI provider is configured and
 * actually responding?" even on a fresh checkout or broken install.
 *
 * Usage:
 *   php scripts/debug/ai_health.php           # report configuration only
 *   php scripts/debug/ai_health.php --probe   # also make a tiny live request to each provider
 *
 * Output is intentionally limited to booleans / HTTP status codes — the
 * script never prints any portion of any API key (no length, no prefix,
 * no last-four). Run inside the project root.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$probe = in_array('--probe', $argv ?? [], true);
$toolSlug = null;
$listOpenRouterModels = null; // null=disabled, ''=list all, 'gemini'=filter
foreach ($argv ?? [] as $arg) {
    if (! is_string($arg)) {
        continue;
    }
    if (str_starts_with($arg, '--tool=')) {
        $toolSlug = substr($arg, strlen('--tool='));
    }
    if ($arg === '--list-openrouter-models') {
        $listOpenRouterModels = '';
    }
    if (str_starts_with($arg, '--list-openrouter-models=')) {
        $listOpenRouterModels = substr($arg, strlen('--list-openrouter-models='));
    }
}

$projectRoot = realpath(__DIR__ . '/../..');
if ($projectRoot === false) {
    fwrite(STDERR, "Could not resolve project root.\n");
    exit(2);
}

$env = parseEnv($projectRoot . DIRECTORY_SEPARATOR . '.env');

// 1. Pull env-level API keys (presence only).
$envKeys = [
    'openai' => isset($env['OPENAI_API_KEY']) && trim((string) $env['OPENAI_API_KEY']) !== '',
    'google' => (isset($env['GEMINI_API_KEY']) && trim((string) $env['GEMINI_API_KEY']) !== '')
                || (isset($env['GOOGLE_API_KEY']) && trim((string) $env['GOOGLE_API_KEY']) !== ''),
    'openrouter' => (isset($env['OPENROUTER_API_KEY']) && trim((string) $env['OPENROUTER_API_KEY']) !== '')
                    || (isset($env['OPEN_ROUTER_API_KEY']) && trim((string) $env['OPEN_ROUTER_API_KEY']) !== ''),
];

// Pull the raw key values for the optional live probe only — they are NEVER
// echoed anywhere; they're held in this scope until the curl call and then
// discarded. The `--probe` flag opt-in is required to load them.
$rawKeys = [];
if ($probe) {
    $rawKeys = [
        'openai' => trim((string) ($env['OPENAI_API_KEY'] ?? '')),
        'google' => trim((string) ($env['GEMINI_API_KEY'] ?? $env['GOOGLE_API_KEY'] ?? '')),
        'openrouter' => trim((string) ($env['OPENROUTER_API_KEY'] ?? $env['OPEN_ROUTER_API_KEY'] ?? '')),
    ];
}

// 2. Pull DB-level API keys (presence only).
$dbKeys = readDbApiKeys($env, $projectRoot, $rawKeys, $probe);

// 3. Compute "configured" = has a key (env OR db) of length >= 10.
$report = [];
foreach (['openai', 'google', 'openrouter'] as $provider) {
    $hasEnv = $envKeys[$provider] ?? false;
    $hasDb = $dbKeys[$provider]['present'] ?? false;
    $configured = ($dbKeys[$provider]['length_ok'] ?? false) || ($envKeys[$provider] && envKeyLengthOk($env, $provider));
    $report[$provider] = [
        'has_env_key' => $hasEnv,
        'has_db_key' => $hasDb,
        'configured' => $configured,
        'live_probe' => null, // filled below if --probe
    ];
}

// 4. Optional live probe.
if ($probe) {
    foreach ($report as $provider => &$row) {
        if (! $row['configured']) {
            $row['live_probe'] = ['skipped' => true, 'reason' => 'not_configured'];
            continue;
        }
        $key = $rawKeys[$provider] !== '' ? $rawKeys[$provider] : ($dbKeys[$provider]['value'] ?? '');
        $row['live_probe'] = liveProbe($provider, $key);
    }
    unset($row);
}

// 5. Print report (no key material anywhere).
echo "AI provider health report\n";
echo "==========================\n";
foreach ($report as $provider => $row) {
    $verdict = $row['configured'] ? 'OK' : 'NOT CONFIGURED';
    printf("%-12s %s\n", $provider, $verdict);
    printf("  env_key_present:   %s\n", $row['has_env_key'] ? 'yes' : 'no');
    printf("  db_key_present:    %s\n", $row['has_db_key']  ? 'yes' : 'no');
    if ($probe) {
        $p = $row['live_probe'];
        if (! empty($p['skipped'])) {
            printf("  live_probe:        skipped (%s)\n", $p['reason']);
        } else {
            printf("  live_probe:        http=%d ok=%s%s\n",
                $p['status'] ?? 0,
                ($p['ok'] ?? false) ? 'yes' : 'no',
                isset($p['error']) ? ' error=' . $p['error'] : ''
            );
        }
    }
}

echo "\nFallback chain (config/vidanexus.php → failover_order): openai, google, openrouter\n";
echo "First provider with `configured=true` is the one AIManager will use unless a per-tool override is set.\n";

if ($toolSlug !== null) {
    echo "\nTool-specific configuration for '{$toolSlug}'\n";
    echo "==========================================\n";
    dumpToolConfig($toolSlug, $env, $projectRoot);
}

if ($listOpenRouterModels !== null) {
    $key = trim((string) ($env['OPENROUTER_API_KEY'] ?? $env['OPEN_ROUTER_API_KEY'] ?? ''));
    echo "\nOpenRouter models available to env key\n";
    echo "======================================\n";
    if ($key === '') {
        echo "  (no OPENROUTER_API_KEY in .env — nothing to query)\n";
    } else {
        listOpenRouterModels($key, $listOpenRouterModels);
    }
}

exit(0);

// ---------------------------------------------------------------------------

/**
 * Parse a Laravel-style .env file into a key/value array. Supports quoted
 * values, comments, and trims whitespace. Does NOT do variable interpolation
 * (which is fine — we only care about API keys here).
 */
function parseEnv(string $path): array
{
    if (! is_file($path)) {
        return [];
    }
    $out = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $k = trim(substr($line, 0, $eq));
        $v = trim(substr($line, $eq + 1));
        // Strip surrounding quotes.
        if (strlen($v) >= 2 && (
            ($v[0] === '"' && substr($v, -1) === '"') ||
            ($v[0] === "'" && substr($v, -1) === "'")
        )) {
            $v = substr($v, 1, -1);
        }
        $out[$k] = $v;
    }
    return $out;
}

function envKeyLengthOk(array $env, string $provider): bool
{
    $candidates = [
        'openai' => ['OPENAI_API_KEY'],
        'google' => ['GEMINI_API_KEY', 'GOOGLE_API_KEY'],
        'openrouter' => ['OPENROUTER_API_KEY', 'OPEN_ROUTER_API_KEY'],
    ];
    foreach ($candidates[$provider] ?? [] as $name) {
        if (isset($env[$name]) && strlen(trim((string) $env[$name])) >= 10) {
            return true;
        }
    }
    return false;
}

/**
 * Open a PDO connection using the .env settings and read the `settings`
 * table for AI provider keys. Returns presence + length-ok booleans per
 * provider, never the value itself (except into $rawKeys for the probe).
 */
function readDbApiKeys(array $env, string $projectRoot, array &$rawKeys, bool $probe): array
{
    $connection = strtolower(trim((string) ($env['DB_CONNECTION'] ?? 'sqlite')));
    $providers = ['openai', 'google', 'openrouter'];
    $empty = array_fill_keys($providers, ['present' => false, 'length_ok' => false]);

    try {
        $pdo = openPdo($connection, $env, $projectRoot);
    } catch (\Throwable $e) {
        fwrite(STDERR, "[warn] Could not connect to DB ({$connection}): " . $e->getMessage() . "\n");
        return $empty;
    }

    $keyMap = [
        'openai' => 'openai_api_key',
        'google' => ['gemini_api_key', 'google_api_key'],
        'openrouter' => ['openrouter_api_key'],
    ];

    $out = $empty;
    foreach ($keyMap as $provider => $keys) {
        foreach ((array) $keys as $settingKey) {
            try {
                $stmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = :k LIMIT 1');
                $stmt->execute(['k' => $settingKey]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                fwrite(STDERR, "[warn] settings table not readable: " . $e->getMessage() . "\n");
                return $empty;
            }

            $value = is_array($row) ? trim((string) ($row['value'] ?? '')) : '';
            if ($value !== '') {
                $out[$provider]['present'] = true;
                $out[$provider]['length_ok'] = strlen($value) >= 10;
                if ($probe && empty($rawKeys[$provider])) {
                    $rawKeys[$provider] = $value;
                }
                break; // first matching key wins
            }
        }
    }

    return $out;
}

function openPdo(string $connection, array $env, string $projectRoot): \PDO
{
    $dsn = match ($connection) {
        'sqlite' => 'sqlite:' . resolveSqlitePath($env, $projectRoot),
        'mysql', 'mariadb' => sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? '127.0.0.1',
            $env['DB_PORT'] ?? '3306',
            $env['DB_DATABASE'] ?? 'laravel'
        ),
        'pgsql' => sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $env['DB_HOST'] ?? '127.0.0.1',
            $env['DB_PORT'] ?? '5432',
            $env['DB_DATABASE'] ?? 'laravel'
        ),
        default => throw new \RuntimeException("Unsupported DB driver: {$connection}"),
    };

    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    return new \PDO($dsn, $user !== '' ? $user : null, $pass !== '' ? $pass : null, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ]);
}

function resolveSqlitePath(array $env, string $projectRoot): string
{
    $configured = trim((string) ($env['DB_DATABASE'] ?? ''));
    if ($configured === '' || strcasecmp($configured, ':memory:') === 0) {
        return $projectRoot . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
    }
    if (preg_match('#^[A-Za-z]:\\\\#', $configured) || str_starts_with($configured, '/')) {
        return $configured;
    }
    return $projectRoot . DIRECTORY_SEPARATOR . ltrim($configured, '\\/');
}

/**
 * Tiny live-probe: send a minimal request to each provider and report the
 * HTTP status code only. Never echoes the key, request body, or response
 * body — just `status` and a boolean `ok` flag.
 */
function liveProbe(string $provider, string $key): array
{
    if ($key === '') {
        return ['skipped' => true, 'reason' => 'empty_key'];
    }

    [$url, $headers] = match ($provider) {
        'openai' => [
            'https://api.openai.com/v1/models',
            ['Authorization: Bearer ' . $key],
        ],
        'google' => [
            'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($key),
            [],
        ],
        'openrouter' => [
            'https://openrouter.ai/api/v1/models',
            ['Authorization: Bearer ' . $key],
        ],
        default => [null, []],
    };
    if ($url === null) {
        return ['skipped' => true, 'reason' => 'unknown_provider'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_NOBODY => false,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    unset($body); // never returned or logged

    if ($status === 0) {
        return ['status' => 0, 'ok' => false, 'error' => $err !== '' ? substr($err, 0, 80) : 'unreachable'];
    }
    return ['status' => $status, 'ok' => $status >= 200 && $status < 300];
}

/**
 * Inspect a single tool's AI configuration: the per-tool chain (with any
 * per-entry api_key MASKED so we only see presence), the strict-mode flag,
 * and any tool-specific provider/model overrides. Helps answer "why is
 * tool X falling through the global failover?" without touching keys.
 */
function dumpToolConfig(string $slug, array $env, string $projectRoot): void
{
    try {
        $connection = strtolower(trim((string) ($env['DB_CONNECTION'] ?? 'sqlite')));
        $pdo = openPdo($connection, $env, $projectRoot);
    } catch (\Throwable $e) {
        echo "[warn] Could not connect to DB: " . $e->getMessage() . "\n";
        return;
    }

    $keys = [
        "{$slug}_ai_chain",
        "{$slug}_ai_chain_strict",
        "{$slug}_provider",
        "{$slug}_model",
    ];

    foreach ($keys as $key) {
        try {
            $stmt = $pdo->prepare('SELECT value, type FROM settings WHERE `key` = :k LIMIT 1');
            $stmt->execute(['k' => $key]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            echo "[warn] settings read failed ({$key}): " . $e->getMessage() . "\n";
            continue;
        }

        if (! $row) {
            printf("  %-40s (not set — default applies)\n", $key);
            continue;
        }

        $value = (string) ($row['value'] ?? '');
        $type = (string) ($row['type'] ?? 'text');

        if (str_ends_with($key, '_ai_chain')) {
            $chain = json_decode($value, true);
            if (! is_array($chain)) {
                printf("  %-40s INVALID JSON\n", $key);
                continue;
            }
            printf("  %-40s %d entr%s\n", $key, count($chain), count($chain) === 1 ? 'y' : 'ies');
            foreach ($chain as $i => $entry) {
                $provider = (string) ($entry['provider'] ?? '?');
                $model = (string) ($entry['model'] ?? '(provider default)');
                $hasKey = isset($entry['api_key']) && trim((string) $entry['api_key']) !== '';
                printf("      [%d] provider=%s  model=%s  has_per_entry_key=%s\n",
                    $i,
                    $provider,
                    $model,
                    $hasKey ? 'yes' : 'no'
                );
            }
            continue;
        }

        if ($type === 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        // The provider/model keys are not secrets — safe to echo verbatim.
        printf("  %-40s %s\n", $key, $value);
    }

    // Log tail: filter to AI-related lines if a laravel.log exists.
    $logFile = $projectRoot . '/storage/logs/laravel.log';
    if (is_file($logFile) && filesize($logFile) > 0) {
        echo "\nRecent ai.* log lines (last 30):\n";
        echo "--------------------------------\n";
        $cmd = ['tail'];
        // tail isn't always available on Windows — use a PHP fallback.
        $lines = tailLines($logFile, 800);
        $matched = [];
        foreach ($lines as $line) {
            if (preg_match('/ai\.(attempt|provider_skipped|failover|all_failed|config_error|success|google|openai|openrouter)/', $line)) {
                $matched[] = $line;
            }
        }
        $matched = array_slice($matched, -30);
        if (empty($matched)) {
            echo "  (no ai.* entries in last ~800 log lines — trigger one more failed request to populate)\n";
        } else {
            foreach ($matched as $line) {
                echo "  " . rtrim($line) . "\n";
            }
        }
    } else {
        echo "\nNo storage/logs/laravel.log yet. Confirm LOG_CHANNEL in .env (typical: 'stack' → single file).\n";
    }
}

/**
 * Hit OpenRouter's /models endpoint with the env key and print the model
 * IDs the user can actually reach. Optionally filter by a substring (case-
 * insensitive). Used to recover from "No endpoints found for X" errors —
 * the canonical alias for a model can change, and this is the fastest way
 * to find the current spelling.
 */
function listOpenRouterModels(string $key, string $filter): void
{
    $ch = curl_init('https://openrouter.ai/api/v1/models');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key],
    ]);
    $body = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        echo "  request failed: http={$status}\n";
        return;
    }

    $payload = json_decode($body, true);
    $models = is_array($payload) ? ($payload['data'] ?? []) : [];
    if (! is_array($models)) {
        echo "  unexpected response shape\n";
        return;
    }

    $filterLc = $filter !== '' ? mb_strtolower($filter) : '';
    $printed = 0;
    foreach ($models as $m) {
        $id = (string) ($m['id'] ?? '');
        if ($id === '') {
            continue;
        }
        if ($filterLc !== '' && ! str_contains(mb_strtolower($id), $filterLc)) {
            continue;
        }
        $ctx = (int) ($m['context_length'] ?? 0);
        printf("  %-60s ctx=%d\n", $id, $ctx);
        $printed++;
    }

    if ($printed === 0) {
        echo "  (no models matched filter " . ($filterLc !== '' ? '"' . $filterLc . '"' : '*') . ")\n";
    } else {
        printf("  --- %d model(s) printed ---\n", $printed);
    }
}

/**
 * Read the last N lines of a file without loading the whole thing into
 * memory. Used by dumpToolConfig() since Windows doesn't ship `tail`.
 *
 * @return array<int, string>
 */
function tailLines(string $path, int $lines): array
{
    $handle = @fopen($path, 'rb');
    if (! $handle) {
        return [];
    }
    $buffer = '';
    $chunkSize = 4096;
    fseek($handle, 0, SEEK_END);
    $size = ftell($handle);
    $newlines = 0;
    while ($size > 0 && $newlines <= $lines) {
        $readSize = min($chunkSize, $size);
        $size -= $readSize;
        fseek($handle, $size);
        $chunk = (string) fread($handle, $readSize);
        $buffer = $chunk . $buffer;
        $newlines = substr_count($buffer, "\n");
    }
    fclose($handle);
    $allLines = preg_split('/\r\n|\r|\n/', $buffer) ?: [];
    return array_slice($allLines, -$lines);
}
