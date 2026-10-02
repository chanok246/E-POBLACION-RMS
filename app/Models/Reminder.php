<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Reminder Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_MEETING = 'Meeting';
    public const TYPE_HEARING = 'Hearing';
    public const TYPE_OPERATION = 'Operation';
    public const TYPE_EVENT = 'Event';
    public const TYPE_BARANGAY_ACTIVITY = 'Barangay Activity';
    public const TYPE_DEADLINE = 'Deadline';
    public const TYPE_INSPECTION = 'Inspection';
    public const TYPE_FOLLOW_UP = 'Follow-up';
    public const TYPE_GENERAL = 'General Reminder';
    public const TYPE_OTHER = 'Other';

    /*
    |--------------------------------------------------------------------------
    | Priorities
    |--------------------------------------------------------------------------
    */

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_IMPORTANT = 'important';
    public const PRIORITY_URGENT = 'urgent';

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'created_by',
        'title',
        'type',
        'notes',
        'location',
        'scheduled_at',
        'priority',
        'reminder_offsets',
        'status',
        'completed_by',
        'completed_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'reminder_offsets' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User who created the reminder.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * User who marked the reminder as completed.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'completed_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    public function isImportant(): bool
    {
        return $this->priority === self::PRIORITY_IMPORTANT;
    }

    /**
     * Determine whether the reminder is still an active scheduled item.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }
}