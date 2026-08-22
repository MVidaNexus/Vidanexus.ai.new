<?php
/**
 * purge_keyword_radar_fallback.php
 *
 * Removes AI Keyword Radar rows that look like raw headlines instead of
 * extracted Target Search Queries. Two heuristics:
 *
 *   1. Rows with source = 'Fallback' (new code tags soft-fail rows this way).
 *   2. Rows whose `keyword` is longer than 8 unicode words (the threshold for
 *      "this is a sentence, not a search query"). Real Target Search Queries
 *      from the AI are 2–6 words; rows above 8 are almost certainly leftover
 *      titles from before the prompt + chain-fallback fix.
 *
 * Usage:
 *   php scripts/maintenance/purge_keyword_radar_fallback.php           # dry-run, all users
 *   php scripts/maintenance/purge_keyword_radar_fallback.php --apply   # actually delete
 *   php scripts/maintenance/purge_keyword_radar_fallback.php --user=1  # restrict to user
 */

require __DIR__ . '/../bootstrap.php';

use Modules\AIKeywordRadar\Models\Keyword;

$apply  = in_array('--apply', $argv, true);
$userId = null;
foreach ($argv as $arg) {
    if (preg_match('/^--user=(\d+)$/', $arg, $m)) {
        $userId = (int) $m[1];
    }
}

$wordCount = static function (string $text): int {
    $parts = preg_split('/[\s\p{P}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    return is_array($parts) ? count($parts) : 0;
};

$baseQuery = Keyword::where(function ($q) {
    $q->where('category', 'Target')->orWhere('category', 'like', 'Target:%');
});
if ($userId !== null) {
    $baseQuery->where('user_id', $userId);
}

$totalScanned = (clone $baseQuery)->count();

$candidates = [];

// Rule 1: explicit Fallback source label (cheap, indexed).
$rule1 = (clone $baseQuery)->where('source', 'Fallback')->get();
foreach ($rule1 as $row) {
    $candidates[$row->id] = ['row' => $row, 'reason' => 'source=Fallback'];
}

// Rule 2: long-form rows (likely raw titles).
$rule2 = (clone $baseQuery)->where('source', '!=', 'Fallback')->get();
foreach ($rule2 as $row) {
    if ($wordCount($row->keyword) > 8) {
        $candidates[$row->id] = ['row' => $row, 'reason' => '>8 words (looks like raw title)'];
    }
}

echo str_repeat('=', 72) . "\n";
echo " AI Keyword Radar — Fallback / Raw-Title Purge\n";
echo "   scanned : {$totalScanned} rows" . ($userId ? " (user #{$userId})" : '') . "\n";
echo "   matched : " . count($candidates) . " rows\n";
echo "   mode    : " . ($apply ? 'APPLY (will delete)' : 'DRY RUN (no changes)') . "\n";
echo str_repeat('=', 72) . "\n\n";

if (empty($candidates)) {
    echo "Nothing to purge.\n";
    return;
}

$sampleLimit = 25;
$sampleShown = 0;
foreach ($candidates as $c) {
    if ($sampleShown >= $sampleLimit) {
        echo "    … (+" . (count($candidates) - $sampleLimit) . " more)\n";
        break;
    }
    $row = $c['row'];
    echo sprintf(
        "  - #%d  user=%d  lang=%s  source=%-9s  reason=%s\n      %s\n",
        $row->id,
        $row->user_id,
        $row->lang,
        $row->source ?: '?',
        $c['reason'],
        mb_substr($row->keyword, 0, 100)
    );
    $sampleShown++;
}

echo "\n";

if (! $apply) {
    echo "Dry run complete. Re-run with --apply to actually delete these rows.\n";
    return;
}

$ids = array_keys($candidates);
$deleted = Keyword::whereIn('id', $ids)->delete();
echo "Deleted {$deleted} rows.\n";

// Bust the per-user/box cache so the dashboard reflects the change instantly.
\Illuminate\Support\Facades\Cache::flush();
echo "Flushed cache.\n";
