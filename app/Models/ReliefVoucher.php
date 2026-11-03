<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReliefVoucher extends Model
{
    use HasFactory;

    protected $table = 'relief_vouchers';

    protected $fillable = [
        'operation_id',
        'beneficiary_id',
        'household_id',
        'voucher_code',
        'assistance_type',
        'allocated_quantity',
        'distribution_schedule',
        'release_date',
        'release_time',
        'release_location',
        'status',
        'qr_verified_at',
        'qr_verified_by',
        'released_at',
        'released_by',
        'claimed_at',
    ];

    protected $casts = [
        'distribution_schedule' => 'datetime',
        'release_date' => 'date',
        'release_time' => 'string',
        'allocated_quantity' => 'integer',
        'qr_verified_at' => 'datetime',
        'released_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function operation(): BelongsTo
    {
        return $this->belongsTo(
            ReliefAssistanceOperation::class,
            'operation_id'
        );
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(
            ReliefOperationBeneficiary::class,
            'beneficiary_id'
        );
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(
            Household::class,
            'household_id'
        );
    }

    public function qrVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'qr_verified_by'
        );
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'released_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return strtolower((string) $this->status) === 'pending';
    }

    public function isGenerated(): bool
    {
        return strtolower((string) $this->status) === 'generated';
    }

    public function isIssued(): bool
    {
        return strtolower((string) $this->status) === 'issued';
    }

    public function isDistributed(): bool
    {
        return strtolower((string) $this->status) === 'distributed';
    }

    public function isClaimed(): bool
    {
        return strtolower((string) $this->status) === 'claimed';
    }

    public function isCancelled(): bool
    {
        return strtolower((string) $this->status) === 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | FINAL STATE
    |--------------------------------------------------------------------------
    */

    /**
     * A claimed or cancelled voucher cannot be edited or reissued.
     */
    public function isFinal(): bool
    {
        return $this->hasBeenClaimed()
            || $this->isCancelled();
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Only generated vouchers can be issued.
     */
    public function canBeIssued(): bool
    {
        return $this->isGenerated()
            && ! $this->isFinal();
    }

    /**
     * Only issued vouchers can be marked as distributed.
     */
    public function canBeDistributed(): bool
    {
        return $this->isIssued()
            && ! $this->isFinal();
    }

    /**
     * Only distributed vouchers can be claimed through
     * a workflow that requires QR verification.
     */
    public function canBeClaimed(): bool
    {
        return $this->isDistributed()
            && ! $this->isFinal()
            && $this->hasQrVerification();
    }

    /**
     * Only generated vouchers are editable.
     */
    public function canBeEdited(): bool
    {
        return $this->isGenerated()
            && ! $this->isFinal();
    }

    /**
     * An issued voucher may be returned to generated status
     * for correction, provided it has not been claimed.
     */
    public function canBeReturnedToGenerated(): bool
    {
        return $this->isIssued()
            && ! $this->isFinal()
            && ! $this->hasQrVerification();
    }

    /**
     * Non-final vouchers may be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return ! $this->isPending()
            && ! $this->isFinal();
    }

    /**
     * Cancelled vouchers must not be printed as valid vouchers.
     * Other statuses remain printable for recordkeeping.
     */
    public function canBePrinted(): bool
    {
        return ! $this->isCancelled();
    }

    public function hasBeenClaimed(): bool
    {
        return $this->isClaimed()
            || $this->claimed_at !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | QR VERIFICATION
    |--------------------------------------------------------------------------
    */

    public function hasQrVerification(): bool
    {
        return $this->qr_verified_at !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | CLAIM
    |--------------------------------------------------------------------------
    */

    /**
     * Record a claim only after QR verification.
     *
     * The normal QR verification controller may also record
     * the claim as part of its own database transaction.
     */
    public function markAsClaimed(): void
    {
        if ($this->isFinal()) {
            throw new LogicException(
                'This voucher is already claimed or cancelled.'
            );
        }

        if (! $this->hasQrVerification()) {
            throw new LogicException(
                'QR verification is required before claiming this voucher.'
            );
        }

        $this->status = 'claimed';
        $this->claimed_at = $this->claimed_at ?? now();

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'pending' => 'PENDING',
            'generated' => 'GENERATED',
            'issued' => 'ISSUED',
            'distributed' => 'DISTRIBUTED',
            'claimed' => 'CLAIMED',
            'cancelled' => 'CANCELLED',
            default => strtoupper((string) $this->status),
        };
    }
}
