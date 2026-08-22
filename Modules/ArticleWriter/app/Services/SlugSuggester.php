<?php

namespace Modules\ArticleWriter\Services;

use Illuminate\Support\Str;

/**
 * SlugSuggester
 * ----------------------------------------------------------------------
 *  Generates SEO-friendly slugs in two scripts:
 *
 *   • English (Latin) — leans on Laravel's Str::slug() with extra cleanup
 *     for unicode quotes / smart punctuation.
 *
 *   • Arabic — a custom slugger that keeps Arabic letters intact (Google
 *     handles UTF-8 path segments), strips tashkeel/tatweel, replaces
 *     every non-letter character with hyphens, normalizes the result.
 *
 *  The class also doubles as a sanitizer for slugs returned by the LLM
 *  (we still defensively re-clean whatever the model produced before
 *  saving it) and provides a deterministic fallback when the model
 *  forgot to emit a [SLUG_*] tag.
 */
class SlugSuggester
{
    /** Maximum slug length — keeps URLs under Google's 75-char comfort zone. */
    public const MAX_LENGTH = 75;

    /**
     * Stop words removed from English slugs to keep them tight + keyword-rich.
     * (Arabic stop words are intentionally NOT stripped: Arabic SEO depends
     * on the natural particle structure to read sensibly.)
     *
     * @var list<string>
     */
    protected const EN_STOPWORDS = [
        'a', 'an', 'the', 'and', 'or', 'but', 'so', 'of', 'in', 'on', 'at',
        'to', 'for', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been',
        'being', 'as', 'that', 'this', 'these', 'those', 'it', 'its',
    ];

    /**
     * Build both slugs from a single source title. Used as the safety-net
     * fallback when the AI response did not include slug tags.
     *
     * @return array{en: string, ar: string}
     */
    public function suggestBoth(string $title, string $articleLanguage = 'en'): array
    {
        $title = trim($title);

        return [
            'en' => $this->english($title),
            'ar' => $this->arabic($title, $articleLanguage),
        ];
    }

    /**
     * Sanitize an arbitrary string into an SEO-safe English slug.
     *
     *  - lowercases everything
     *  - transliterates Arabic / accented Latin to ASCII (Str::slug handles this)
     *  - strips stop words
     *  - collapses repeated hyphens
     *  - clamps to MAX_LENGTH at a word boundary
     */
    public function english(string $title): string
    {
        if (trim($title) === '') {
            return '';
        }

        // Normalise smart punctuation that Str::slug doesn't always know about.
        $title = strtr($title, [
            "\u{2018}" => "'", "\u{2019}" => "'",
            "\u{201C}" => '"', "\u{201D}" => '"',
            "\u{2013}" => '-', "\u{2014}" => '-',
        ]);

        // Str::slug handles transliteration → ASCII, lowercase, hyphenate.
        $slug = Str::slug($title, '-');
        if ($slug === '') {
            return '';
        }

        // Drop English stop words ("the-best-marketing-agency" → "best-marketing-agency").
        $parts = array_filter(
            explode('-', $slug),
            fn ($word) => $word !== '' && ! in_array($word, self::EN_STOPWORDS, true)
        );

        $slug = implode('-', $parts);
        if ($slug === '') {
            $slug = Str::slug($title, '-'); // fallback — never return empty
        }

        return $this->clamp($slug);
    }

    /**
     * Sanitize an arbitrary string into an SEO-safe Arabic slug. Keeps
     * native Arabic letters and normalises everything else to hyphens.
     *
     * If the source title contains no Arabic characters at all and the
     * caller's article language is not Arabic, we just return ''. The
     * controller treats an empty Arabic slug as "skip" so we never emit
     * a transliterated mess like "/top-marketing-agency-in-egypt" twice.
     */
    public function arabic(string $title, string $articleLanguage = 'ar'): string
    {
        if (trim($title) === '') {
            return '';
        }

        $hasArabic = (bool) preg_match('/\p{Arabic}/u', $title);
        if (! $hasArabic && $articleLanguage !== 'ar') {
            return '';
        }

        $clean = $title;

        // 1. Strip tashkeel (harakat) — fatha, kasra, damma, sukun, shadda, tanween, etc.
        //    Unicode block U+064B..U+065F + U+0670 (superscript alif) + U+06D6..U+06ED (Quranic marks).
        $clean = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $clean) ?? $clean;

        // 2. Strip tatweel (ـ) — the Arabic letter elongator, never SEO-useful.
        $clean = str_replace("\u{0640}", '', $clean);

        // 3. Lowercase any Latin characters that snuck in (English brand names, etc.).
        $clean = mb_strtolower($clean, 'UTF-8');

        // 4. Replace every char that isn't an Arabic letter, ASCII letter, or digit
        //    with a single hyphen. Punctuation, emojis, control chars all get squashed.
        $clean = preg_replace('/[^\p{Arabic}a-z0-9]+/u', '-', $clean) ?? $clean;

        // 5. Collapse multiple hyphens and trim leading/trailing ones.
        $clean = preg_replace('/-+/', '-', $clean) ?? $clean;
        $clean = trim($clean, '-');

        return $this->clamp($clean);
    }

    /**
     * Treat whatever the AI returned as a slug, regardless of language,
     * by detecting script and routing to the correct cleaner.
     */
    public function sanitizeFromAi(string $slug, string $articleLanguage = 'en'): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        // The model sometimes wraps slugs in slashes / quotes / backticks.
        $slug = trim($slug, "/`'\"");

        return preg_match('/\p{Arabic}/u', $slug)
            ? $this->arabic($slug, 'ar')
            : $this->english($slug);
    }

    /**
     * Trim the slug to MAX_LENGTH on a hyphen boundary so we never
     * truncate mid-word (which Google penalises in long URLs).
     */
    protected function clamp(string $slug): string
    {
        if (mb_strlen($slug) <= self::MAX_LENGTH) {
            return $slug;
        }

        $cut = mb_substr($slug, 0, self::MAX_LENGTH);
        $lastHyphen = mb_strrpos($cut, '-');
        if ($lastHyphen !== false && $lastHyphen > 20) {
            $cut = mb_substr($cut, 0, $lastHyphen);
        }

        return rtrim($cut, '-');
    }
}
