<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('ledger:reconcile')->hourly();
Schedule::command('queue:work --stop-when-empty --tries=2 --max-time=50')->everyMinute()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('radar:clean-duplicates {user_id=1}', function () {
    $userId = (int) $this->argument('user_id');
    $del = \Illuminate\Support\Facades\DB::table('ai_keywords')->where('user_id', $userId)->where(function($q) {
        $q->where('keyword', 'like', '% في')
          ->orWhere('keyword', 'like', '% من')
          ->orWhere('keyword', 'like', '% على')
          ->orWhere('keyword', 'like', '%..')
          ->orWhere('keyword', 'like', '%:')
          ->orWhere('keyword', 'like', '%-');
    })->delete();

    $kws = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $userId)->where('category', 'Target')->where('lang', 'ar')->get();
    $service = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
    $arr = $kws->map(function($k) {
        return ['id' => $k->id, 'text' => $k->keyword, 'headline_title' => $k->headline_title];
    })->toArray();

    $kept = $service->filterSimilarKeywords($arr, 0.50, $userId);
    $keptIds = array_column($kept, 'id');

    $deletedDupes = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $userId)
        ->where('category', 'Target')
        ->where('lang', 'ar')
        ->whereNotIn('id', $keptIds)
        ->delete();

    $remaining = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $userId)->where('category', 'Target')->where('lang', 'ar')->count();
    $this->info("Purged suffix fluff: {$del}, Purged duplicate angles: {$deletedDupes}, Remaining distinct keywords: {$remaining}");
});
