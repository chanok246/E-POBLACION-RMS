<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    protected $fillable = [
        'personnel_id',
        'attendance_date',
        'time_in',
        'time_out',
        'time_in_method',
        'time_out_method',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
        ];
    }

    /**
     * Prevent permanent deletion of official attendance history.
     */
    protected static function booted(): void
    {
        static::deleting(function (AttendanceRecord $record): void {
            throw new LogicException(
                'Attendance records cannot be deleted. Preserve the original record and use an authorized correction instead.'
            );
        });
    }

    /**
     * Personnel who owns this attendance record.
     */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    /**
     * Emergency or early departure authorization.
     */
    public function departureAuthorization(): HasOne
    {
        return $this->hasOne(
            AttendanceDepartureAuthorization::class,
            'attendance_record_id'
        );
    }

    /**
     * Check whether the personnel has timed in.
     */
    public function hasTimedIn(): bool
    {
        return $this->time_in !== null;
    }

    /**
     * Check whether the personnel has timed out.
     */
    public function hasTimedOut(): bool
    {
        return $this->time_out !== null;
    }

    /**
     * Check whether attendance is still open.
     */
    public function isOpen(): bool
    {
        return $this->hasTimedIn() && ! $this->hasTimedOut();
    }

    /**
     * Scope: records for a specific attendance date.
     */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('attendance_date', $date);
    }
}
