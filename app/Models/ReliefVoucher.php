<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return strtolower(
            (string) $this->status
        ) === 'pending';
    }

    public function isGenerated(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'generated';
    }

    public function isIssued(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'issued';
    }

    public function isDistributed(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'distributed';
    }

    public function isClaimed(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'claimed';
    }

    public function isCancelled(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * A generated voucher can be issued.
     */
    public function canBeIssued(): bool
    {
        return $this->isGenerated();
    }

    /**
     * An issued voucher can be distributed.
     */
    public function canBeDistributed(): bool
    {
        return $this->isIssued();
    }

    /**
     * A distributed voucher can be claimed,
     * but the controller must also verify that
     * QR verification has already occurred.
     */
    public function canBeClaimed(): bool
    {
        return $this->isDistributed();
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

    public function markAsClaimed(): void
    {
        $this->status = 'claimed';
        $this->claimed_at = now();

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match (
            strtolower((string) $this->status)
        ) {
            'pending' =>
                'PENDING',

            'generated' =>
                'GENERATED',

            'issued' =>
                'ISSUED',

            'distributed' =>
                'DISTRIBUTED',

            'claimed' =>
                'CLAIMED',

            'cancelled' =>
                'CANCELLED',

            default =>
                strtoupper(
                    (string) $this->status
                ),
        };
    }
}
