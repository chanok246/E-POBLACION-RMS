<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReliefAssistanceOperation extends Model
{
    use HasFactory;

    protected $table = 'relief_assistance_operations';

    protected $fillable = [
        'operation_code',
        'operation_name',
        'description',

        'assistance_type',
        'assistance_description',

        /*
        |--------------------------------------------------------------------------
        | MASTERLIST FILTER SETTINGS
        |--------------------------------------------------------------------------
        |
        | These store the filters selected when preparing the operation's
        | masterlist so the same beneficiary pool can be reopened later.
        |
        */

        'beneficiary_filter',
        'purok_filter',

        'source_name',
        'source_reference',

        'assistance_received_date',
        'quantity_received',
        'quantity_unit',

        'quantity_allocated',
        'allocation_basis',
        'allocation_notes',

        'distribution_date',
        'distribution_time',
        'distribution_location',

        'status',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assistance_received_date' => 'date',
        'distribution_date' => 'date',

        'quantity_received' => 'integer',
        'quantity_allocated' => 'integer',

        'distribution_time' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(
            ReliefOperationBeneficiary::class,
            'operation_id'
        );
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(
            ReliefVoucher::class,
            'operation_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OPERATION TYPE HELPERS
    |--------------------------------------------------------------------------
    |
    | The system now recognizes three main assistance modes:
    |
    | 1. In-Kind Distribution
    | 2. Cash / Financial Assistance
    | 3. Service / Social Assistance
    |
    | Voucher generation is applicable to the first two when required.
    | Service-based activities such as Medical Missions normally do not
    | require vouchers.
    |
    */

    public function isInKind(): bool
    {
        $type = strtolower(
            trim((string) $this->assistance_type)
        );

        return in_array($type, [
            'in_kind',
            'in-kind',
            'in kind',
            'relief_goods',
            'relief goods',
            'food_pack',
            'food pack',
        ], true);
    }

    public function isCashAssistance(): bool
    {
        $type = strtolower(
            trim((string) $this->assistance_type)
        );

        return in_array($type, [
            'cash',
            'cash_assistance',
            'cash assistance',
            'financial_assistance',
            'financial assistance',
            'emergency_cash',
            'emergency cash',
        ], true);
    }

    public function isServiceAssistance(): bool
    {
        $type = strtolower(
            trim((string) $this->assistance_type)
        );

        return in_array($type, [
            'service',
            'service_assistance',
            'service assistance',
            'medical',
            'medical_mission',
            'medical mission',
            'dental',
            'dental_mission',
            'dental mission',
            'vaccination',
            'vaccination_drive',
            'vaccination drive',
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | VOUCHER HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Determines whether this operation can use QR vouchers.
     *
     * In-kind and cash/financial assistance can use vouchers.
     * Service activities such as medical missions do not use vouchers
     * by default.
     */
    public function supportsVouchers(): bool
    {
        return $this->isInKind()
            || $this->isCashAssistance();
    }

    /**
     * Determines whether this operation is intended for physical
     * goods distribution.
     */
    public function isPhysicalGoodsDistribution(): bool
    {
        return $this->isInKind();
    }

    /**
     * Determines whether this operation involves money.
     */
    public function isFinancialAssistance(): bool
    {
        return $this->isCashAssistance();
    }

    /**
     * Determines whether this operation records a service or activity
     * instead of distributing goods or money.
     */
    public function isServiceBased(): bool
    {
        return $this->isServiceAssistance();
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return strtolower((string) $this->status) === 'draft';
    }

    public function isVerification(): bool
    {
        return strtolower((string) $this->status) === 'verification';
    }

    public function isReady(): bool
    {
        return strtolower((string) $this->status) === 'ready';
    }

    public function isAllocated(): bool
    {
        return strtolower((string) $this->status) === 'allocated';
    }

    public function isDistribution(): bool
    {
        return strtolower((string) $this->status) === 'distribution';
    }

    public function isCompleted(): bool
    {
        return strtolower((string) $this->status) === 'completed';
    }

    public function isCancelled(): bool
    {
        return strtolower((string) $this->status) === 'cancelled';
    }

    /*
    |--------------------------------------------------------------------------
    | QUANTITY HELPERS
    |--------------------------------------------------------------------------
    */

    public function getRemainingQuantityAttribute(): int
    {
        $received = (int) (
            $this->quantity_received ?? 0
        );

        $allocated = (int) (
            $this->quantity_allocated ?? 0
        );

        return max(
            0,
            $received - $allocated
        );
    }

    public function hasReceivedAssistance(): bool
    {
        return (int) (
            $this->quantity_received ?? 0
        ) > 0;
    }

    public function hasRemainingQuantity(): bool
    {
        return $this->remaining_quantity > 0;
    }

    public function hasShortage(): bool
    {
        return (
            (int) ($this->quantity_allocated ?? 0)
        ) > (
            (int) ($this->quantity_received ?? 0)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DISPLAY HELPERS
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match (
            strtolower((string) $this->status)
        ) {
            'draft' => 'DRAFT',
            'verification' => 'VERIFICATION',
            'ready' => 'READY',
            'allocated' => 'ALLOCATED',
            'distribution' => 'DISTRIBUTION',
            'completed' => 'COMPLETED',
            'cancelled' => 'CANCELLED',
            default => strtoupper(
                (string) $this->status
            ),
        };
    }

    public function getAssistanceTypeLabelAttribute(): string
    {
        return ucwords(
            str_replace(
                '_',
                ' ',
                (string) $this->assistance_type
            )
        );
    }

    /**
     * Human-readable operation mode.
     */
    public function getOperationModeLabelAttribute(): string
    {
        if ($this->isInKind()) {
            return 'IN-KIND RELIEF DISTRIBUTION';
        }

        if ($this->isCashAssistance()) {
            return 'CASH / FINANCIAL ASSISTANCE';
        }

        if ($this->isServiceAssistance()) {
            return 'SERVICE / SOCIAL ASSISTANCE';
        }

        return 'ASSISTANCE OPERATION';
    }
}
