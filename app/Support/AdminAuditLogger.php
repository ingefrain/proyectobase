<?php

namespace App\Support;

use App\Models\AdminAuditEvent;

class AdminAuditLogger
{
    private const REDACTED_KEYS = [
        'password', 'password_confirmation', 'remember_token', 'session_version',
        'token', 'secret', 'cookie', 'hash',
    ];

    public function record(
        string $action,
        string $entityType,
        int|string|null $entityId,
        ?array $before = null,
        ?array $after = null,
        ?array $context = null,
    ): AdminAuditEvent {
        return AdminAuditEvent::query()->create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId === null ? null : (string) $entityId,
            'before' => $this->sanitize($before),
            'after' => $this->sanitize($after),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'context' => $this->sanitize($context),
            'created_at' => now(),
        ]);
    }

    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $safe = [];
        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), self::REDACTED_KEYS, true)) {
                continue;
            }

            $safe[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $safe;
    }
}
