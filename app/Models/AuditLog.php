<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * Audit logs are write-once records.
     *
     * There is intentionally no updated_at column in the database.
     */
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'record_id',
        'record_reference',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Prevent normal Eloquent updates to audit records.
     *
     * Audit records must remain unchanged after creation.
     */
    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \RuntimeException(
                'Audit logs are immutable and cannot be updated.'
            );
        });

        static::deleting(function (): void {
            throw new \RuntimeException(
                'Audit logs are immutable and cannot be deleted.'
            );
        });
    }

    /**
     * Scope audit logs by module.
     */
    public function scopeForModule(
        Builder $query,
        string $module
    ): Builder {
        return $query->where('module', $module);
    }

    /**
     * Scope audit logs by action.
     */
    public function scopeForAction(
        Builder $query,
        string $action
    ): Builder {
        return $query->where('action', $action);
    }

    /**
     * Scope audit logs by user.
     */
    public function scopeByUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where('user_id', $userId);
    }
}
