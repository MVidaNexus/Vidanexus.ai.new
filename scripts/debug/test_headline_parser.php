<?php

/**
 * Standalone smoke test for HeadlineService::extractHeadlines() — runs
 * without booting Laravel so it works even when vendor/ is missing.
 *
 * We re-implement the parser inline because the real class depends on
 * Laravel's Log facade. If the inline copy and the real method ever drift
 * apart, the comment at the top of this file is the canary.
 *
 * Run: php scripts/debug/test_headline_parser.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/_headline_parser_inline.php';

$cases = [
    'clean_json' => [
        'input' => '{"headlines":[{"headline":"OpenAI announces new model GPT-5","sentiment":"surprise","entities":["OpenAI","GPT-5"],"lsi_keywords":["AI","model"],"thumbnail_suggestion":"GPT-5 logo on dark gradient"}]}',
        'expect_count' => 1,
        'expect_first' => 'OpenAI announces new model GPT-5',
    ],
    'markdown_fenced' => [
        'input' => "Sure! Here you go:\n```json\n{\"headlines\":[{\"headline\":\"Gold prices surge in Egypt amid global tension\",\"sentiment\":\"neutral\"}]}\n```\nHope this helps.",
        'expect_count' => 1,
        'expect_first' => 'Gold prices surge in Egypt amid global tension',
    ],
    'double_escaped' => [
        'input' => '{\"headlines\":[{\"headline\":\"Egypt fuel prices climb after new round of hikes\",\"sentiment\":\"negative\",\"entities\":[\"Egypt\",\"fuel\"]}]}',
        'expect_count' => 1,
        'expect_first' => 'Egypt fuel prices climb after new round of hikes',
    ],
    'json_in_string' => [
        'input' => '"{\"headlines\":[{\"headline\":\"Al-Ahly faces Zamalek in derby tonight\",\"sentiment\":\"positive\"}]}"',
        'expect_count' => 1,
        'expect_first' => 'Al-Ahly faces Zamalek in derby tonight',
    ],
    'bare_list' => [
        'input' => '[{"headline":"Inflation eases in May","sentiment":"positive"},{"headline":"Tourism revenue hits record","sentiment":"positive"}]',
        'expect_count' => 2,
        'expect_first' => 'Inflation eases in May',
    ],
    'plain_text_lines' => [
        'input' => "1. Crypto market gains 12% this week\n2. Bitcoin holds steady above $70k\n3. Ethereum upgrade slated for July",
        'expect_count' => 3,
        'expect_first' => 'Crypto market gains 12% this week',
    ],
    'malformed_json_with_unbalanced_braces' => [
        // The OLD recursive-regex parser would extract something nonsensical
        // here and the fallback would treat the whole blob as one headline.
        // The new parser should log+return empty (zero entries).
        'input' => '{"headlines":[{"headline":"Headline with } stray brace inside text","sentiment":"neutral"',
        'expect_count' => 0,
        'expect_first' => null,
    ],
    'real_world_dump_screenshot' => [
        // Approximation of what the user's screenshot showed — JSON on one
        // line, returned without proper escaping. Old parser produced one
        // giant "headline" card with this entire string as text. Should now
        // parse cleanly or return zero (never the full blob as one card).
        'input' => '{"headlines":[{"headline":"القناة 12 العبرية تكشف عن سقوط مسيرتين","sentiment":"neutral","entities":["القناة 12","حزب الله"],"lsi_keywords":["مسيرتين","سقوط"],"thumbnail_suggestion":"صورة لمسيرة"}]}',
        'expect_count' => 1,
        'expect_first' => 'القناة 12 العبرية تكشف عن سقوط مسيرتين',
    ],
];

$pass = 0;
$fail = 0;
foreach ($cases as $name => $case) {
    $out = extractHeadlinesInline($case['input']);
    $count = count($out);
    $firstHeadline = $out[0]['headline'] ?? null;

    $okCount = $count === $case['expect_count'];
    $okFirst = $case['expect_first'] === null
        ? ($firstHeadline === null)
        : ($firstHeadline === $case['expect_first']);

    if ($okCount && $okFirst) {
        $pass++;
        printf("PASS  %-44s  count=%d\n", $name, $count);
    } else {
        $fail++;
        printf("FAIL  %-44s  count=%d (expected %d)  first=%s\n",
            $name,
            $count,
            $case['expect_count'],
            var_export($firstHeadline, true)
        );
    }
}

printf("\n%d passed, %d failed\n", $pass, $fail);
exit($fail === 0 ? 0 : 1);
