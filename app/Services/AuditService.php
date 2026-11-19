<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        string $action,
        Model $entity,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null
    ): AuditLog {
        $user = $actor ?? auth()->user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => strtoupper($action),
            'entity_type' => class_basename($entity),
            'entity_id' => $entity->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'System / CLI',
            'created_at' => now(),
        ]);
    }
}
