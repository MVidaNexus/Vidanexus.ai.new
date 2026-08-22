<?php

/**
 * Inline copy of HeadlineService's parser helpers, stripped of Laravel
 * dependencies so the smoke test can run without vendor/. Keep in sync with
 * Modules/DiscoverHeadlines/app/Services/HeadlineService.php — the real
 * code is the source of truth; this file just lets us validate edge cases
 * before deploying.
 */

declare(strict_types=1);

function extractHeadlinesInline(string $text): array
{
    $decoded = decodeJsonFlexibleInline($text);

    if (is_array($decoded)) {
        return normalizeHeadlineListInline($decoded);
    }

    return extractHeadlinesPlainTextInline($text);
}

function decodeJsonFlexibleInline(string $text): mixed
{
    $trimmed = trim($text);

    if (preg_match('/```(?:json|markdown|text)?\s*(.*?)\s*```/s', $trimmed, $m)) {
        $trimmed = trim($m[1]);
    }

    $candidates = [$trimmed];

    $first = strpos($trimmed, '{');
    $last  = strrpos($trimmed, '}');
    if ($first !== false && $last !== false && $last > $first) {
        $candidates[] = substr($trimmed, $first, $last - $first + 1);
    }

    foreach (array_values($candidates) as $candidate) {
        $candidates[] = stripslashes($candidate);
    }

    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }

        $tryDecoded = json_decode($candidate, true);

        if (is_string($tryDecoded)
            && (str_contains($tryDecoded, '"headlines"') || str_contains($tryDecoded, '"headline"'))
        ) {
            $tryDecoded = json_decode($tryDecoded, true);
        }

        if (is_array($tryDecoded)) {
            return $tryDecoded;
        }
    }

    return null;
}

function normalizeHeadlineListInline(array $decoded): array
{
    $items = $decoded['headlines']
        ?? $decoded['data']
        ?? $decoded['results']
        ?? $decoded;

    if (! is_array($items)) {
        return [];
    }

    $out = [];
    foreach ($items as $item) {
        if (! is_array($item)) {
            continue;
        }

        $headline = (string) ($item['headline'] ?? $item['title'] ?? $item['text'] ?? $item['keyword'] ?? '');
        $headline = trim($headline);

        if ($headline === '' || looksLikeJsonFragmentInline($headline)) {
            continue;
        }

        $out[] = [
            'headline' => $headline,
            'sentiment' => (string) ($item['sentiment'] ?? 'Neutral'),
            'entities' => asStringListInline($item['entities'] ?? $item['keywords'] ?? []),
            'lsi_keywords' => asStringListInline($item['lsi_keywords'] ?? $item['lsi'] ?? []),
            'thumbnail_suggestion' => (string) ($item['thumbnail_suggestion'] ?? $item['thumbnail'] ?? $item['visual_angle'] ?? ''),
        ];
    }

    return $out;
}

function extractHeadlinesPlainTextInline(string $text): array
{
    $extracted = [];
    foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }
        $len = mb_strlen($line);
        if ($len < 10 || $len > 200) {
            continue;
        }
        if (looksLikeJsonFragmentInline($line)) {
            continue;
        }
        $extracted[] = [
            'headline' => preg_replace('/^\d+[\.\)]\s*/', '', $line),
            'sentiment' => 'Factual',
            'entities' => [],
            'lsi_keywords' => [],
            'thumbnail_suggestion' => '',
        ];
    }
    return $extracted;
}

function looksLikeJsonFragmentInline(string $line): bool
{
    if (preg_match('/[{}\[\]]/', $line) === 1) {
        return true;
    }
    if (preg_match('/"\s*(headline|headlines|sentiment|entities|lsi_keywords|thumbnail_suggestion)"\s*:/i', $line) === 1) {
        return true;
    }
    return false;
}

function asStringListInline(mixed $value): array
{
    if (is_string($value)) {
        $value = preg_split('/\s*,\s*/', $value) ?: [];
    }
    if (! is_array($value)) {
        return [];
    }
    $out = [];
    foreach ($value as $item) {
        if (is_string($item)) {
            $s = trim($item);
            if ($s !== '') {
                $out[] = $s;
            }
            continue;
        }
        if (is_array($item)) {
            $s = trim((string) ($item['name'] ?? $item['text'] ?? $item['value'] ?? ''));
            if ($s !== '') {
                $out[] = $s;
            }
        }
    }
    return $out;
}
