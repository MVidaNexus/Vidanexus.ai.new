<?php

namespace Modules\AIKeywordRadar\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\AIKeywordRadar\Models\Keyword;

class KeywordPayload
{
    /**
     * @return array{text: string, source: string, headline_title: ?string, published_at: ?string, synced_at: ?string, created_at: string}
     */
    public static function fromModel(Keyword $kw): array
    {
        return [
            'text' => $kw->keyword,
            'source' => $kw->source ?? 'AI',
            'headline_title' => $kw->headline_title ?: null,
            'published_at' => $kw->published_at ? $kw->published_at->toDateTimeString() : null,
            'synced_at' => $kw->synced_at ? $kw->synced_at->toDateTimeString() : null,
            'created_at' => $kw->created_at->toDateTimeString(),
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
        return max(40, min(200, (int) \App\Models\Setting::get('ai-keyword-radar_max_headlines', 120)));
    }

    public static function syncLockKey(int $userId, string $lang, ?string $boxId = null): string
    {
        return 'sync_lock_'.$userId.'_'.$lang.($boxId ? "_{$boxId}" : '');
    }

    /** Max seconds a sync lock may live before we treat it as stale (crashed worker). */
    public static function syncLockStaleSeconds(): int
    {
        return 660;
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
