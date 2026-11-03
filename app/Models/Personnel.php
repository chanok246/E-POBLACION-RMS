<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use LogicException;

class Personnel extends Model
{
    protected $table = 'personnel';

    protected $fillable = [
        'user_id',
        'personnel_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'position',
        'pin_hash',
        'pin_updated_at',
        'qr_token_hash',
        'qr_issued_at',
        'qr_revoked_at',
        'status',
    ];

    protected $hidden = [
        'pin_hash',
        'qr_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'pin_updated_at' => 'datetime',
            'qr_issued_at' => 'datetime',
            'qr_revoked_at' => 'datetime',
        ];
    }

    /**
     * Prevent permanent deletion.
     *
     * Personnel records must be deactivated instead so their
     * attendance history and identity remain preserved.
     */
    protected static function booted(): void
    {
        static::deleting(function (Personnel $personnel): void {
            throw new LogicException(
                'Personnel records cannot be deleted. Deactivate the personnel record instead.'
            );
        });
    }

    /**
     * Existing RMS account, if this personnel is Captain or Secretary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Attendance records belonging to this personnel.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Set a six-digit PIN using Laravel's secure password hashing.
     */
    public function setAttendancePin(string $pin): void
    {
        if (! preg_match('/^\d{6}$/', $pin)) {
            throw new \InvalidArgumentException(
                'The attendance PIN must contain exactly six digits.'
            );
        }

        $this->pin_hash = Hash::make($pin);
        $this->pin_updated_at = now();
    }

    /**
     * Verify the supplied six-digit attendance PIN.
     */
    public function verifyAttendancePin(string $pin): bool
    {
        if (! preg_match('/^\d{6}$/', $pin)) {
            return false;
        }

        if (empty($this->pin_hash)) {
            return false;
        }

        return Hash::check($pin, $this->pin_hash);
    }

    /**
     * Check whether this personnel is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check whether this personnel has an active QR token.
     */
    public function hasActiveQrToken(): bool
    {
        return ! empty($this->qr_token_hash)
            && $this->qr_revoked_at === null;
    }

    /**
     * Verify a raw QR token against its stored hash.
     */
    public function verifyQrToken(string $token): bool
    {
        if (! $this->hasActiveQrToken() || $token === '') {
            return false;
        }

        return hash_equals(
            $this->qr_token_hash,
            hash('sha256', $token)
        );
    }

    /**
     * Get the personnel's complete display name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }

    /**
     * Scope: active personnel only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
