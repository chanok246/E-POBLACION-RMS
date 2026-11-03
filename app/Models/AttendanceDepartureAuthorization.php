<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AttendanceDepartureAuthorization extends Model
{
    protected $table = 'attendance_departure_authorizations';

    protected $fillable = [
        'attendance_record_id',
        'actual_departure_at',
        'reason',
        'authorized_by',
        'authorized_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'actual_departure_at' => 'datetime',
            'authorized_at' => 'datetime',
        ];
    }

    /**
     * Prevent permanent deletion of authorization history.
     */
    protected static function booted(): void
    {
        static::deleting(function (AttendanceDepartureAuthorization $authorization): void {
            throw new LogicException(
                'Departure authorization records cannot be deleted.'
            );
        });
    }

    /**
     * Attendance record covered by this authorization.
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Existing RMS user who authorized the departure.
     */
    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
