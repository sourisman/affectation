<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;

/** Journalisation légère des actions sensibles (audit). */
trait ActivityAware
{
    protected function log(string $action, ?string $entity = null, ?string $entityId = null, ?string $description = null, array $meta = []): void
    {
        try {
            (new ActivityLog())->record($action, $entity, $entityId, $description, $meta);
        } catch (\Throwable $e) {
            error_log('[audit] ' . $e->getMessage());
        }
    }
}
