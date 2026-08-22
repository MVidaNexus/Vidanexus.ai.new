Scripts layout (project root: parent of scripts/)

bootstrap.php
  Load Laravel once. Required by other scripts via:
  $app = require __DIR__.'/../bootstrap.php';

maintenance/
  Operational fixes and server hygiene. Review before running on production.
  - apply_patches.php / apply_sync_fix.php — AI Keyword Radar job + locks + queue
  - check_perms.php — writable check (POSIX owner only on Linux/macOS)
  - clear_view_cache.php — clears storage/framework/views compiled blades
  - fix_sync.php — aggressive DB cache/jobs cleanup + route sanity (edit if stack changes)
  - verify_fix.php — lock/queue snapshot + AppServiceProvider checks

debug/
  Diagnostics and load tests. Many print secrets (key suffixes) or hit live AI/RSS.
  Prefer: php scripts/debug/<name>.php from CLI on staging.
  - check_settings.php — DB settings for keyword radar
  - debug_ai.php — sample AI call
  - debug_sync_lock.php — locks, jobs, log tails (read-only)
  - debug_sync_visual.php — full headline+AI pipeline (optional ?user_id=; may log in as user #1)
  - test_proc.php — proc_open + artisan queue:work --once
  - test_radar.php — internal GET global-news-monitor + HTML checks
  - test_reflection.php — KeywordService method probe
  - test_sync_now.php — dispatchSync for first user with competitors
  - test_topics.php — Google News RSS probe
  - tool_errors.php — JSON ToolError rows (?tool=slug&limit=5) replaces test_errors/test_errors2
  - verify_speed.php — Route::dispatch sync POST as user #1

legacy/
  One-off mutators kept for history only. Do not run unless you intend to change data/config.

experimental/
  Throwaway probes (Google Trends, etc.). Not part of the application.

experimental/python/
  Local utilities; tmp_purge_color.py walks the repo root from this file location.

If the web server document root is the repository root (not public/), block HTTP access to
scripts/ via nginx/apache rules, or move these off the deploy artifact.
