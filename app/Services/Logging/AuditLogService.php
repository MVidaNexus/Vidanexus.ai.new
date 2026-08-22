<?php

namespace App\Services\Logging;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    public function log(
        ?int $userId,
        string $action,
        string $entityType,
        string|int|null $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $context = [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ];

        AuditLog::create($context);
        Log::channel('audit')->info('Audit event', $context);
    }
}
