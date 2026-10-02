<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Record an important system action.
     *
     * Audit logs are append-only. This service only creates
     * new records and does not provide update/delete methods.
     */
    public function log(
        string $action,
        string $module,
        ?Model $record = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?Request $request = null
    ): AuditLog {
        $request ??= request();

        $user = $request->user();

        return AuditLog::create([
            'user_id' => $user?->id,

            'action' => $action,

            'module' => $module,

            'record_id' => $record?->getKey(),

            'record_reference' => $this->resolveRecordReference($record),

            'old_values' => $oldValues,

            'new_values' => $newValues,

            'ip_address' => $request->ip(),

            'user_agent' => $request->userAgent(),

            'description' => $description,
        ]);
    }

    /**
     * Create an audit log using explicit record information.
     *
     * Useful when the record is unavailable or when a custom
     * reference needs to be recorded.
     */
    public function logAction(
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $recordReference = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?Request $request = null
    ): AuditLog {
        $request ??= request();

        $user = $request->user();

        return AuditLog::create([
            'user_id' => $user?->id,

            'action' => $action,

            'module' => $module,

            'record_id' => $recordId,

            'record_reference' => $recordReference,

            'old_values' => $oldValues,

            'new_values' => $newValues,

            'ip_address' => $request->ip(),

            'user_agent' => $request->userAgent(),

            'description' => $description,
        ]);
    }

    /**
     * Resolve a human-readable reference for the affected record.
     */
    protected function resolveRecordReference(?Model $record): ?string
    {
        if (! $record) {
            return null;
        }

        $referenceAttributes = [
            'request_code',
            'record_code',
            'voucher_code',
            'household_number',
            'resident_code',
            'case_number',
            'event_code',
            'id',
        ];

        foreach ($referenceAttributes as $attribute) {
            $value = $record->getAttribute($attribute);

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return $record->getKey()
            ? (string) $record->getKey()
            : null;
    }
}
