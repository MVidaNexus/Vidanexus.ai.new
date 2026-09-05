<?php

namespace Modules\AIKeywordRadar\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\AIKeywordRadar\Models\Keyword;

class KeywordPayload
{
    /**
     * @return array{text: string, source: string, headline_title: ?string, published_at: ?string, synced_at: ?string, created_at: string, intent: array, is_high_traffic: bool}
     */
    public static function fromModel(Keyword $kw): array
    {
        $lang = $kw->lang ?? 'ar';
        $intent = self::detectSearchIntent($kw->keyword, $lang);
        $trafficAnalysis = self::analyzeTrafficPotential($kw->keyword, $kw->published_at, $lang);

        return [
            'text' => $kw->keyword,
            'source' => $kw->source ?? 'AI',
            'headline_title' => $kw->headline_title ?: null,
            'published_at' => $kw->published_at ? $kw->published_at->toDateTimeString() : null,
            'synced_at' => $kw->synced_at ? $kw->synced_at->toDateTimeString() : null,
            'created_at' => $kw->created_at->toDateTimeString(),
            'intent' => $intent,
            'is_high_traffic' => $trafficAnalysis['is_high'],
            'traffic_score' => $trafficAnalysis['score'],
            'traffic_reasons' => $trafficAnalysis['reasons'],
        ];
    }

    /**
     * Determine if a keyword has high traffic potential / viral commercial search intent
     */
    public static function isHighTraffic(string $text, string $lang = 'ar'): bool
    {
        $analysis = self::analyzeTrafficPotential($text, null, $lang);
        return $analysis['is_high'];
    }

    /**
     * Multi-Dimensional Traffic & Trend Potential Analyzer
     * Evaluates semantic intent, user demand volume, searchability, and freshness
     *
     * @return array{score: int, is_high: bool, reasons: list<string>}
     */
    public static function analyzeTrafficPotential(string $text, ?Carbon $publishedAt = null, string $lang = 'ar'): array
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        // Disqualify empty or single-word fragments or overly long sentences
        if ($wordCount < 2 || $wordCount > 7) {
            return ['score' => 20, 'is_high' => false, 'reasons' => ['length_mismatch']];
        }

        // Severe penalty for journalistic commentary/op-eds
        if (preg_match('/(أسئلة حول|قراءة في|تأملات|نظرة على|شهادات عن|أسرار وخفايا|ماذا وراء|التفاصيل الدامية|كواليس|مفاجأة غير متوقعة)/u', $text)) {
            return ['score' => 10, 'is_high' => false, 'reasons' => ['commentary_fluff']];
        }

        $score = 0;
        $reasons = [];

        // 1. High Demand Pillars
        // Pillar A: Markets, Gold, Currencies, Economy & Shopping (up to 40 pts)
        if (preg_match('/(سعر|اسعار|أسعار|ذهب|عيار|دولار|ريال|عملات|بترول|تراجع|ارتفاع|تضخم|فوائد|شهادات|بورصة|شراء|خصم|عروض|تخفيضات|مواصفات|تقسيط|price|gold|dollar|rates|crypto|market|cost|cheapest|deal|deals|discount)/u', $text)) {
            $score += 35;
            $reasons[] = 'commercial_finance';
        }

        // Pillar B: Services, Housing, Jobs & Public Needs (up to 40 pts)
        if (preg_match('/(معاشات|مرتبات|صرف|تكافل وكرامة|تموين|شقق|إسكان|سكن مصر|دار مصر|جنة|حجز|طرح|تأشيرة|تذاكر|طيران|قرعة|حج|عمرة|الطقس|أرصاد|درجات الحرارة|وظائف|وظائف شاغرة|وظائف خالية|شروط|خطوات|رابط|بوابة|الاستعلام|jobs|salary|housing|visa|tickets|weather|apply|portal|booking)/u', $text)) {
            $score += 35;
            $reasons[] = 'public_services';
        }

        // Pillar C: Results, Admissions, Education & Exams (up to 40 pts)
        if (preg_match('/(نتيجة|نتائج|تنسيق|أوائل|دبلومات|ثانوية عامة|امتحانات|جدول امتحانات|شهادة|كليات|جامعات|result|results|exam|schedule|grades|admissions)/u', $text)) {
            $score += 35;
            $reasons[] = 'education_results';
        }

        // Pillar D: Sports, Matches, Viral Teams & Athletes (up to 40 pts)
        if (preg_match('/(مباراة|مباريات|موعد مباراة|بث مباشر|القنوات الناقلة|تشكيل|أهداف|اهداف|ملخص|ترتيب الدوري|صفقة|انتقال|الأهلي|الزمالك|الهلال|النصر|الاتحاد|ريال مدريد|برشلونة|ليفربول|مانشستر|محمد صلاح|كولر|حسام حسن|ميسي|رونالدو|match|live|stream|score|highlights|standings|vs|fc)/u', $text)) {
            $score += 35;
            $reasons[] = 'sports_viral';
        }

        // Pillar E: Tech Gadgets, Cars & Consumer Reviews (up to 30 pts)
        if (preg_match('/(iphone|آيفون|سامسونج|شاومي|هاتف|مواصفات|مقارنة|سيارات|تطبيق|تحديث|android|ios|xiaomi|samsung|car|review|specs)/u', $text)) {
            $score += 30;
            $reasons[] = 'tech_consumer';
        }

        // Pillar F: How-to & Actionable Procedures (up to 25 pts)
        if (preg_match('/(كيف|كيفية|طريقة|شرح|حل مشكلة|دليل|خطوات|how to|guide|steps|solution)/u', $text)) {
            $score += 20;
            $reasons[] = 'actionable_how_to';
        }

        // 2. Freshness & Trend Momentum (up to 35 pts)
        if ($publishedAt) {
            $diffHours = now()->diffInHours($publishedAt);
            if ($diffHours <= 2) {
                $score += 35;
                $reasons[] = 'breaking_freshness';
            } elseif ($diffHours <= 6) {
                $score += 25;
                $reasons[] = 'momentum_freshness';
            } elseif ($diffHours <= 24) {
                $score += 15;
                $reasons[] = 'daily_freshness';
            }
        } else {
            $score += 20; // Default fresh crawl
        }

        $finalScore = min(100, $score);
        $isHigh = ($finalScore >= 50);

        return [
            'score' => $finalScore,
            'is_high' => $isHigh,
            'reasons' => $reasons,
        ];
    }

    /**
     * Detect Search Intent (Commercial, Informational, Trending, Navigational, General)
     */
    public static function detectSearchIntent(string $text, string $lang = 'ar'): array
    {
        $text = mb_strtolower(trim($text), 'UTF-8');

        // 0. Editorial / Rhetorical Commentary (Classified as General)
        if (preg_match('/(أسئلة حول|قراءة في|تأملات|نظرة على|شهادات عن|أسرار وخفايا|ماذا وراء|التفاصيل الدامية)/u', $text)) {
            return [
                'type' => 'general',
                'label' => ($lang === 'ar') ? 'عام' : 'General',
                'icon' => 'fas fa-search',
                'badge_bg' => 'rgba(255, 255, 255, 0.08)',
                'badge_border' => 'rgba(255, 255, 255, 0.15)',
                'badge_color' => '#94a3b8',
            ];
        }

        // 1. Commercial / Transactional (Buying intent, pricing, stores, reviews)
        if (preg_match('/(سعر|اسعار|أسعار|شراء|اشتري|متجر|سوق|عروض|عرض|خصم|كود|تخفيض|ارخص|أرخص|افضل|أفضل|مقارنة|مراجعة|مواصفات|تقسيط|للبيع|حجز|تكلفة|رسوم|كم سعر|ذهب|دولار|بترول|buy|price|cost|pricing|best|top|cheap|cheapest|deal|deals|discount|coupon|promo|store|shop|for sale|vs|review|reviews|order|hire|rent|booking)/u', $text)) {
            return [
                'type' => 'commercial',
                'label' => ($lang === 'ar') ? 'شرائي / تجاري' : 'Commercial',
                'icon' => 'fas fa-shopping-cart',
                'badge_bg' => 'rgba(16, 185, 129, 0.15)',
                'badge_border' => 'rgba(16, 185, 129, 0.35)',
                'badge_color' => '#10b981',
            ];
        }

        // 2. Trending / News (Live, breaking, match, score, today, date)
        if (preg_match('/(مباشر|بث مباشر|مباراة|مباريات|اهداف|أهداف|ملخص|ترتيب|موعد|تشكيل|معلق|صفقة|انتقال|قرعة|حفل|عاجل|زلزال|حادث|وفاة|live|stream|match|score|today|now|breaking|highlights)/u', $text)) {
            return [
                'type' => 'trending',
                'label' => ($lang === 'ar') ? 'تريند' : 'Trending',
                'icon' => 'fas fa-bolt',
                'badge_bg' => 'rgba(245, 158, 11, 0.15)',
                'badge_border' => 'rgba(245, 158, 11, 0.35)',
                'badge_color' => '#f59e0b',
            ];
        }

        // 3. Informational (How to, guide, questions, tutorial, explain)
        if (preg_match('/(كيف|كيفية|طريقة|شرح|معنى|ما هو|ما هي|ماذا|لماذا|اسباب|أسباب|اعراض|أعراض|فوائد|أضرار|خطوات|دليل|نصائح|حل|علاج|شروط|تنسيق|نتائج|جدول|how|what|why|when|guide|tips|steps|tutorial|explain|meaning|symptoms|benefits|causes|solution|remedy|requirements)/u', $text)) {
            return [
                'type' => 'informational',
                'label' => ($lang === 'ar') ? 'معلوماتي' : 'Informational',
                'icon' => 'fas fa-info-circle',
                'badge_bg' => 'rgba(14, 165, 233, 0.15)',
                'badge_border' => 'rgba(14, 165, 233, 0.35)',
                'badge_color' => '#38bdf8',
            ];
        }

        // 4. Navigational (Login, portal, app, link, site)
        if (preg_match('/(تسجيل|دخول|موقع|رابط|لينك|تطبيق|تحميل|بوابة|منصة|login|signin|portal|app|download|link|website|official)/u', $text)) {
            return [
                'type' => 'navigational',
                'label' => ($lang === 'ar') ? 'تصفحي' : 'Navigational',
                'icon' => 'fas fa-compass',
                'badge_bg' => 'rgba(168, 85, 247, 0.15)',
                'badge_border' => 'rgba(168, 85, 247, 0.35)',
                'badge_color' => '#c084fc',
            ];
        }

        return [
            'type' => 'general',
            'label' => ($lang === 'ar') ? 'عام' : 'General',
            'icon' => 'fas fa-search',
            'badge_bg' => 'rgba(255, 255, 255, 0.08)',
            'badge_border' => 'rgba(255, 255, 255, 0.15)',
            'badge_color' => '#94a3b8',
        ];
    }

    /**
     * @return list<array{text: string, source: string, headline_title: ?string, published_at: ?string, synced_at: ?string, created_at: string}>
     */
    public static function fromCollection(Collection $keywords): array
    {
        return $keywords->map(fn (Keyword $kw) => self::fromModel($kw))->values()->all();
    }

    public static function retentionHours(): int
    {
        return max(1, (int) \App\Models\Setting::get('ai-keyword-radar_retention_hours', 24));
    }

    public static function retentionLimit(): Carbon
    {
        return now()->subHours(self::retentionHours());
    }

    /**
     * Keywords visible in the radar UI: seen recently via sync, creation, or article date.
     */
    public static function applyRetentionScope($query)
    {
        $limit = self::retentionLimit();

        return $query->where(function ($q) use ($limit) {
            $q->where('synced_at', '>=', $limit)
                ->orWhere('created_at', '>=', $limit)
                ->orWhere('published_at', '>=', $limit);
        });
    }

    public static function maxHeadlinesForAi(): int
    {
        return max(15, min(100, (int) \App\Models\Setting::get('ai-keyword-radar_max_headlines', 30)));
    }

    public static function syncLockKey(int $userId, string $lang, ?string $boxId = null): string
    {
        return 'sync_lock_'.$userId.'_'.$lang.($boxId ? "_{$boxId}" : '');
    }

    /** Max seconds a sync lock may live before we treat it as stale (crashed worker). */
    public static function syncLockStaleSeconds(): int
    {
        return 75;
    }

    public static function acquireSyncLock(int $userId, string $lang, ?string $boxId = null): void
    {
        $key = self::syncLockKey($userId, $lang, $boxId);
        Cache::put($key, ['started_at' => time()], self::syncLockStaleSeconds());
    }

    public static function releaseSyncLock(int $userId, string $lang, ?string $boxId = null): void
    {
        Cache::forget(self::syncLockKey($userId, $lang, $boxId));
    }

    /**
     * True when a non-stale sync lock exists. Stale locks (crashed PHP / queue) are cleared automatically.
     */
    public static function isSyncLocked(int $userId, string $lang, ?string $boxId = null): bool
    {
        $key = self::syncLockKey($userId, $lang, $boxId);
        $lock = Cache::get($key);

        if ($lock === null) {
            return false;
        }

        // Legacy locks stored as plain `true` (no timestamp) — clear on read so
        // crashed syncs from the old job queue cannot block the UI forever.
        if ($lock === true) {
            Cache::forget($key);

            return false;
        }

        $startedAt = is_array($lock) ? (int) ($lock['started_at'] ?? 0) : 0;

        if ($startedAt <= 0) {
            Cache::forget($key);

            return false;
        }

        if ((time() - $startedAt) > self::syncLockStaleSeconds()) {
            Cache::forget($key);

            return false;
        }

        return true;
    }

    /**
     * Seconds left before a stuck lock is auto-cleared. Does not mutate the lock.
     */
    public static function syncLockRemainingSeconds(int $userId, string $lang, ?string $boxId = null): int
    {
        $key = self::syncLockKey($userId, $lang, $boxId);
        $lock = Cache::get($key);

        if ($lock === null || $lock === true) {
            return self::syncLockStaleSeconds();
        }

        $startedAt = is_array($lock) ? (int) ($lock['started_at'] ?? 0) : 0;
        if ($startedAt <= 0) {
            return self::syncLockStaleSeconds();
        }

        return max(30, self::syncLockStaleSeconds() - (time() - $startedAt));
    }
}
