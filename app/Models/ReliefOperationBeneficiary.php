<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReliefOperationBeneficiary extends Model
{
    use HasFactory;

    protected $table = 'relief_operation_beneficiaries';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'operation_id',

        /*
        |--------------------------------------------------------------------------
        | BENEFICIARY IDENTITY
        |--------------------------------------------------------------------------
        |
        | resident_id = actual resident/person selected as beneficiary
        | household_id = household context of that resident
        |
        | We keep BOTH because an assistance operation may be:
        | - individual/resident-based
        | - household-based
        |
        */

        'resident_id',
        'household_id',

        /*
        |--------------------------------------------------------------------------
        | BENEFICIARY VERIFICATION
        |--------------------------------------------------------------------------
        */

        'beneficiary_status',
        'field_reference',
        'verification_notes',

        'validated_by',
        'validated_at',

        /*
        |--------------------------------------------------------------------------
        | ASSISTANCE ALLOCATION
        |--------------------------------------------------------------------------
        */

        'assistance_status',
        'allocated_quantity',
        'allocation_notes',

        'allocated_by',
        'allocated_at',

        /*
        |--------------------------------------------------------------------------
        | DISTRIBUTION / CLAIM
        |--------------------------------------------------------------------------
        */

        'distributed_at',
        'claimed_at',

        /*
        |--------------------------------------------------------------------------
        | AUDIT
        |--------------------------------------------------------------------------
        */

        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'validated_at' => 'datetime',

        'allocated_at' => 'datetime',

        'distributed_at' => 'datetime',

        'claimed_at' => 'datetime',

        'allocated_quantity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Relief assistance operation this beneficiary belongs to.
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(
            ReliefAssistanceOperation::class,
            'operation_id'
        );
    }

    /**
     * Actual resident/person selected as beneficiary.
     *
     * Example:
     *
     * Student Assistance
     *     ↓
     * Maria Dela Cruz
     *     ↓
     * resident_id
     *
     * This allows multiple residents from the same household
     * to be selected independently.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class,
            'resident_id'
        );
    }

    /**
     * Household associated with the beneficiary.
     *
     * This is intentionally retained even when resident_id exists.
     *
     * It provides:
     * - Household Number
     * - Household Head
     * - Purok
     * - Household Address
     * - Household context
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(
            Household::class,
            'household_id'
        );
    }

    /**
     * User who validated the beneficiary.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'validated_by'
        );
    }

    /**
     * User who allocated assistance.
     */
    public function allocator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'allocated_by'
        );
    }

    /**
     * User who created the beneficiary record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * User who last updated the beneficiary record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BENEFICIARY VERIFICATION STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the beneficiary was field verified.
     */
    public function isFieldVerified(): bool
    {
        return strtolower(
            (string) $this->beneficiary_status
        ) === 'field_verified';
    }

    /**
     * Determine whether the beneficiary was validated by the encoder.
     */
    public function isEncoderValidated(): bool
    {
        return strtolower(
            (string) $this->beneficiary_status
        ) === 'encoder_validated';
    }

    /**
     * Determine whether the beneficiary is confirmed.
     */
    public function isConfirmed(): bool
    {
        return strtolower(
            (string) $this->beneficiary_status
        ) === 'confirmed';
    }

    /**
     * Determine whether the beneficiary was removed.
     */
    public function isRemoved(): bool
    {
        return strtolower(
            (string) $this->beneficiary_status
        ) === 'removed';
    }

    /*
    |--------------------------------------------------------------------------
    | ASSISTANCE STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the beneficiary is still awaiting assistance.
     */
    public function isAwaitingAssistance(): bool
    {
        return strtolower(
            (string) $this->assistance_status
        ) === 'awaiting_assistance';
    }

    /**
     * Determine whether assistance has been allocated.
     */
    public function isAllocated(): bool
    {
        return strtolower(
            (string) $this->assistance_status
        ) === 'allocated';
    }

    /**
     * Determine whether a voucher has been generated.
     */
    public function isVoucherGenerated(): bool
    {
        return strtolower(
            (string) $this->assistance_status
        ) === 'voucher_generated';
    }

    /**
     * Determine whether assistance has been distributed.
     */
    public function isDistributed(): bool
    {
        return strtolower(
            (string) $this->assistance_status
        ) === 'distributed';
    }

    /**
     * Determine whether assistance has been claimed.
     */
    public function isClaimed(): bool
    {
        return strtolower(
            (string) $this->assistance_status
        ) === 'claimed';
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY LABELS
    |--------------------------------------------------------------------------
    */

    /**
     * Human-readable beneficiary status.
     */
    public function getBeneficiaryStatusLabelAttribute(): string
    {
        return match (
            strtolower((string) $this->beneficiary_status)
        ) {
            'field_verified' =>
                'FIELD VERIFIED',

            'encoder_validated' =>
                'ENCODER VALIDATED',

            'confirmed' =>
                'CONFIRMED',

            'removed' =>
                'REMOVED',

            default =>
                strtoupper(
                    (string) $this->beneficiary_status
                ),
        };
    }

    /**
     * Human-readable assistance status.
     */
    public function getAssistanceStatusLabelAttribute(): string
    {
        return match (
            strtolower((string) $this->assistance_status)
        ) {
            'awaiting_assistance' =>
                'AWAITING ASSISTANCE',

            'allocated' =>
                'ALLOCATED',

            'voucher_generated' =>
                'VOUCHER GENERATED',

            'distributed' =>
                'DISTRIBUTED',

            'claimed' =>
                'CLAIMED',

            default =>
                strtoupper(
                    (string) $this->assistance_status
                ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW PERMISSION HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether this beneficiary can receive an allocation.
     *
     * Requirements:
     * - Must be confirmed.
     * - Must not be removed.
     * - Must still be awaiting assistance.
     */
    public function canBeAllocated(): bool
    {
        return $this->isConfirmed()
            && ! $this->isRemoved()
            && $this->isAwaitingAssistance();
    }

    /**
     * Determine whether a voucher can be generated.
     *
     * Requirements:
     * - Must be confirmed.
     * - Must already have an allocation.
     * - Allocated quantity must be greater than zero.
     */
    public function canGenerateVoucher(): bool
    {
        return $this->isConfirmed()
            && $this->isAllocated()
            && (int) $this->allocated_quantity > 0;
    }

    /**
     * Determine whether the beneficiary can be marked
     * as distributed.
     */
    public function canBeDistributed(): bool
    {
        return $this->isVoucherGenerated();
    }

    /**
     * Determine whether the beneficiary can be marked
     * as claimed.
     */
    public function canBeClaimed(): bool
    {
        return $this->isDistributed();
    }
}
