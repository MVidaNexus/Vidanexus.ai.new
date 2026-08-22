<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Tracks long-running work in cache for HTTP poll / SPA clients.
 *
 * Typical flow:
 * 1. Controller: $id = $tasks->start($userId, 'export_csv', ['input' => ...]);
 * 2. Controller: HeavyJob::dispatch($userId, $id);
 * 3. Return JSON: { "task_id": $id, "poll_url": route('background-tasks.show', $id) }
 * 4. Job handle(): $tasks->touch($id, ['status' => 'running', 'message' => '...']);
 * 5. Job end: $tasks->complete($id, ['download_url' => ...]) or $tasks->fail($id, 'reason');
 * 6. Frontend: poll GET until status is completed|failed (see public/js/background-task-client.js).
 */
class BackgroundTaskService
{
    protected string $prefix;

    protected int $ttlSeconds;

    public function __construct()
    {
        $this->prefix = (string) config('background_tasks.cache_key_prefix', 'bg_task');
        $this->ttlSeconds = (int) config('background_tasks.ttl_seconds', 3600);
    }

    protected function key(string $id): string
    {
        return "{$this->prefix}:{$id}";
    }

    /**
     * Create a new task record; returns the task id (UUID).
     *
     * @param  array<string, mixed>  $meta
     */
    public function start(int $userId, string $type, array $meta = [], ?string $id = null): string
    {
        $id = $id ?: (string) Str::uuid();

        $payload = [
            'user_id' => $userId,
            'type' => $type,
            'status' => 'queued',
            'message' => 'Queued',
            'progress' => 0,
            'meta' => $meta,
            'result' => null,
            'error' => null,
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put($this->key($id), $payload, $this->ttlSeconds);

        return $id;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function touch(string $id, array $attributes): void
    {
        $existing = Cache::get($this->key($id), []);
        if ($existing === []) {
            return;
        }

        unset($attributes['user_id']);
        $merged = array_merge($existing, $attributes, [
            'updated_at' => now()->toIso8601String(),
        ]);

        Cache::put($this->key($id), $merged, $this->ttlSeconds);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function complete(string $id, array $result = []): void
    {
        $this->touch($id, [
            'status' => 'completed',
            'message' => 'Done',
            'progress' => 100,
            'result' => $result,
            'error' => null,
        ]);
    }

    public function fail(string $id, string $errorMessage): void
    {
        $this->touch($id, [
            'status' => 'failed',
            'message' => $errorMessage,
            'error' => $errorMessage,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getForUser(string $id, int $userId): ?array
    {
        $data = Cache::get($this->key($id));
        if (! is_array($data) || ! isset($data['user_id'])) {
            return null;
        }

        if ((int) $data['user_id'] !== $userId) {
            return null;
        }

        return $data;
    }
}
