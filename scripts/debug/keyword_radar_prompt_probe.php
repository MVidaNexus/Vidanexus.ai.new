<?php
/**
 * keyword_radar_prompt_probe.php
 *
 * One-shot diagnostic for the AI Keyword Radar prompt path. Use this when
 * the cards on the radar are showing raw titles instead of extracted
 * keywords — it proves whether the AI is actually splitting titles now,
 * after the prompt + chain-fallback fixes.
 *
 * Usage:
 *     php scripts/debug/keyword_radar_prompt_probe.php [user_id] [lang] [time_filter]
 *
 * Defaults:
 *     user_id     = 1
 *     lang        = ar
 *     time_filter = 60m
 *
 * What it does (in order):
 *   1. Prints the active per-tool config (provider, model, prompt summary,
 *      keywords_per_headline) so you can see exactly what would be sent.
 *   2. Deletes existing rows in the Target/Target:* category for the
 *      requested user+lang. This wipes the stale "raw title" rows the
 *      pre-fix fallback wrote — otherwise the new sync just adds NEW
 *      good rows next to the bad ones, and you can't tell which is which.
 *   3. Pulls competitor headlines for the requested window, sends them
 *      through the AI extractor, and prints the parsed keywords.
 *   4. Prints the FULL prompt + the AI's raw response when DEBUG=1, so
 *      you can paste it into ChatGPT and verify the model would split
 *      titles the same way.
 */

require __DIR__ . '/../bootstrap.php';

use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;
use App\Core\AI\AIManager;
use App\Models\Setting;

set_time_limit(300);

$userId = (int) ($argv[1] ?? 1);
$lang   = strtolower((string) ($argv[2] ?? 'ar'));
$filter = (string) ($argv[3] ?? '60m');
$debug  = (bool) getenv('DEBUG');

echo str_repeat('=', 70) . "\n";
echo " AI Keyword Radar — Prompt + Extraction Probe\n";
echo "   user_id={$userId}  lang={$lang}  time_filter={$filter}  DEBUG=" . ($debug ? '1' : '0') . "\n";
echo str_repeat('=', 70) . "\n\n";

// ------------------------------------------------------------------
// 1. Active config
// ------------------------------------------------------------------
$perHeadline = (int) Setting::get('ai-keyword-radar_keywords_per_headline', 3);
$provider    = Setting::get('ai-keyword-radar_provider') ?: 'openrouter';
$model       = Setting::get('ai-keyword-radar_model') ?: '(default for provider)';
$dbPrompt    = (string) Setting::get('ai-keyword-radar_prompt');
$promptSrc   = $dbPrompt !== '' ? 'admin-authored (DB)' : 'built-in default';
$promptHead  = $dbPrompt !== '' ? mb_substr(trim($dbPrompt), 0, 220) . (mb_strlen($dbPrompt) > 220 ? '…' : '') : '(uses code default)';

echo "[1/4] Active configuration\n";
echo "    keywords_per_headline : {$perHeadline}\n";
echo "    provider              : {$provider}\n";
echo "    model                 : {$model}\n";
echo "    prompt source         : {$promptSrc}\n";
echo "    prompt head           : {$promptHead}\n\n";

// ------------------------------------------------------------------
// 2. Wipe stale rows for this user+lang
// ------------------------------------------------------------------
$category = 'Target';
$existing = Keyword::where('user_id', $userId)
    ->where('lang', $lang)
    ->where(function ($q) {
        $q->where('category', 'Target')->orWhere('category', 'like', 'Target:%');
    });
$beforeCount = (clone $existing)->count();

echo "[2/4] Wiping {$beforeCount} existing rows for user #{$userId}, lang={$lang} …\n";
$existing->delete();
echo "    done.\n\n";

// ------------------------------------------------------------------
// 3. End-to-end sync
// ------------------------------------------------------------------
echo "[3/4] Running sync (this calls the SAME path as Refresh Radar) …\n";
/** @var KeywordService $service */
$service = app(KeywordService::class);

$started = microtime(true);
$result  = $service->syncKeywords(500, $lang, $userId, $filter);
$elapsed = round(microtime(true) - $started, 2);

echo "    sync finished in {$elapsed}s\n";
echo "    result: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n\n";

// ------------------------------------------------------------------
// 4. Inspect what landed in the DB
// ------------------------------------------------------------------
echo "[4/4] Keywords now stored for user #{$userId}, lang={$lang} (most recent first):\n";

$rows = Keyword::where('user_id', $userId)
    ->where('lang', $lang)
    ->where(function ($q) {
        $q->where('category', 'Target')->orWhere('category', 'like', 'Target:%');
    })
    ->orderByDesc('synced_at')
    ->limit(30)
    ->get();

if ($rows->isEmpty()) {
    echo "    (none — AI returned nothing AND the fallback also produced nothing)\n";
} else {
    foreach ($rows as $i => $r) {
        $line = sprintf(
            "    %02d. [%-9s] %s",
            $i + 1,
            $r->source ?: '?',
            $r->keyword
        );
        echo $line . "\n";
    }
}

echo "\n";
echo "Total stored after sync: " . Keyword::where('user_id', $userId)->where('lang', $lang)
        ->where(function ($q) {
            $q->where('category', 'Target')->orWhere('category', 'like', 'Target:%');
        })->count() . "\n\n";

// ------------------------------------------------------------------
// Heuristic: are these extracted keywords or just raw titles?
// Counts unicode word boundaries so it works for Arabic too.
// ------------------------------------------------------------------
$wordCount = static function (string $text): int {
    $parts = preg_split('/[\s\p{P}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    return is_array($parts) ? count($parts) : 0;
};

$looksLikeTitles = 0;
foreach ($rows as $r) {
    if ($wordCount($r->keyword) > 7) {
        $looksLikeTitles++;
    }
}

if ($rows->isNotEmpty() && $looksLikeTitles >= max(1, (int) ($rows->count() * 0.4))) {
    echo "⚠  More than 40% of stored rows are >7 words long. The AI either:\n";
    echo "     • still returned nothing and the fallback wrote raw titles, or\n";
    echo "     • is not honoring the 'extract N per headline' rule.\n";
    echo "   Re-run with DEBUG=1 to dump the raw prompt + response:\n";
    echo "     Windows PowerShell:  \$env:DEBUG=1; php scripts/debug/keyword_radar_prompt_probe.php {$userId} {$lang} {$filter}\n";
    echo "     bash/zsh:            DEBUG=1 php scripts/debug/keyword_radar_prompt_probe.php {$userId} {$lang} {$filter}\n";
} elseif ($rows->isNotEmpty()) {
    echo "✓  Output looks like extracted Target Search Queries, not raw titles.\n";
}

// ------------------------------------------------------------------
// DEBUG: print the prompt+response by hand-rolling a minimal call
// against the same AI chain used by extractKeywordsWithAI.
// ------------------------------------------------------------------
if ($debug) {
    echo "\n" . str_repeat('-', 70) . "\n";
    echo " DEBUG dump — direct AI call against the SAME chain configuration\n";
    echo str_repeat('-', 70) . "\n";

    // Pick a handful of fresh headlines so we don't burn tokens on 500 titles.
    $sample = $rows->take(5)->pluck('keyword')->all();
    if (empty($sample)) {
        echo "(no rows available to test with — skipping AI probe)\n";
    } else {
        $titlesText = '';
        foreach ($sample as $i => $t) {
            $titlesText .= ($i + 1) . ". [probe] {$t}\n";
        }
        $langInstruction = $lang === 'en' ? 'English' : 'Arabic';
        $count = count($sample);

        $prompt = ($dbPrompt !== '')
            ? str_replace(
                ['[Headlines]', '[headlines]', '[lang]', '[KeywordsPerHeadline]', '[keywords_per_headline]'],
                [$titlesText, $titlesText, $langInstruction, (string) $perHeadline, (string) $perHeadline],
                $dbPrompt
            )
            : "Extract {$perHeadline} Target Search Queries per headline. Output language: {$langInstruction}.\n\nHeadlines:\n{$titlesText}\n\nReturn ONLY a JSON array: [{\"index\":1,\"keyword\":\"...\"}]";

        echo "--- PROMPT (" . strlen($prompt) . " chars) ---\n{$prompt}\n";

        /** @var AIManager $ai */
        $ai = app(AIManager::class);
        try {
            $resp = $ai->generate('ai-keyword-radar', $prompt, [
                'provider' => $provider,
                'temperature' => 0.2,
                'max_tokens' => 2000,
            ]);
            echo "\n--- AI RESPONSE (provider_used=" . ($resp['provider_used'] ?? '?') . ", model_used=" . ($resp['model_used'] ?? '?') . ") ---\n";
            echo ($resp['text'] ?? '(empty)') . "\n";
        } catch (\Throwable $e) {
            echo "\n--- AI CALL FAILED ---\n";
            echo get_class($e) . ': ' . $e->getMessage() . "\n";
            if (property_exists($e, 'attempts')) {
                echo "Attempts:\n";
                foreach ($e->attempts as $a) {
                    echo "  - provider={$a['provider']} model=" . ($a['model'] ?? 'default') . " error={$a['error']}\n";
                }
            }
        }
    }
}
