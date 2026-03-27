<?php

namespace Modules\AIKeywordRadar\Console\Commands;

use Illuminate\Console\Command;
use Modules\AIKeywordRadar\Services\KeywordService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SyncKeywords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'aikeywordradar:sync';

    /**
     * The console command description.
     */
    protected $description = 'Sync AI Keyword Radar trends from competitors for all active users';

    /**
     * Execute the console command.
     */
    public function handle(KeywordService $service)
    {
        $this->info('Starting AI Keyword Radar background sync...');
        Log::info('AI Keyword Radar background sync started.');

        // Check if global competitors are set
        $hasGlobalCompetitors = !empty(trim(\App\Models\Setting::get('ai-keyword-radar_competitors', ''))) || 
                               !empty(trim(\App\Models\Setting::get('ai-keyword-radar_rss_feeds', '')));

        // Find all users who have competitors configured OR if global ones exist, process active users
        $users = User::whereNotNull('settings')->get()->filter(function($user) use ($hasGlobalCompetitors) {
            $settings = $user->settings ?? [];
            $hasUserCompetitors = !empty($settings['keywords_competitors']) || !empty($settings['keywords_competitors_en']);
            return $hasUserCompetitors || ($hasGlobalCompetitors && $user->isActive()); // Only sync global for active users to save resources
        });

        $this->info('Found ' . $users->count() . ' users with competitor configurations.');

        foreach ($users as $user) {
            $this->info("Processing User #{$user->id} ({$user->email})");
            
            $settings = $user->settings ?? [];
            
            // Sync Arabic if configured
            if (!empty($settings['keywords_competitors'])) {
                try {
                    $this->info('  - Syncing Arabic trends...');
                    $result = $service->syncKeywords(500, 'ar', $user->id);
                    $this->info("    - Added {$result['saved']} new keywords from {$result['headlines']} headlines.");
                } catch (\Exception $e) {
                    $this->error("    - Error syncing Arabic: " . $e->getMessage());
                }
            }

            // Sync English if configured and enabled
            if (!empty($settings['keywords_competitors_en']) && !empty($settings['enable_keywords_en'])) {
                try {
                    $this->info('  - Syncing English trends...');
                    $result = $service->syncKeywords(500, 'en', $user->id);
                    $this->info("    - Added {$result['saved']} new keywords from {$result['headlines']} headlines.");
                } catch (\Exception $e) {
                    $this->error("    - Error syncing English: " . $e->getMessage());
                }
            }
        }

        $this->info('Sync completed.');
        Log::info('AI Keyword Radar background sync completed.');
    }
}
