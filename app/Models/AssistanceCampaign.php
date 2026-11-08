<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistanceCampaign extends Model
{
    protected $fillable = [
        'campaign_code',
        'name',
        'description',
        'assistance_type',
        'start_date',
        'end_date',
        'assistance_amount',
        'assistance_package',
        'status',
        'barangay',
        'municipality',
        'province',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assistance_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User who created the campaign.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /**
     * User who last updated the campaign.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    /**
     * Relief vouchers generated under this campaign.
     *
     * This relationship is retained for compatibility
     * with the existing voucher records.
     */
    public function reliefVouchers(): HasMany
    {
        return $this->hasMany(
            ReliefVoucher::class,
            'program_id'
        );
    }

    /**
     * Actual relief assistance operations under this campaign.
     *
     * One campaign/program may have multiple actual
     * household-based mass distribution operations.
     */
    public function reliefAssistanceOperations(): HasMany
    {
        return $this->hasMany(
            ReliefAssistanceOperation::class,
            'campaign_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return strtolower((string) $this->status) === 'draft';
    }

    public function isActive(): bool
    {
        return strtolower((string) $this->status) === 'active';
    }

    public function isClosed(): bool
    {
        return strtolower((string) $this->status) === 'closed';
    }

    public function isCancelled(): bool
    {
        return strtolower((string) $this->status) === 'cancelled';
    }

    /**
     * Determine whether the campaign can currently issue vouchers.
     */
    public function canIssueVouchers(): bool
    {
        return $this->isActive();
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'draft' => 'DRAFT',
            'active' => 'ACTIVE',
            'closed' => 'CLOSED',
            'cancelled' => 'CANCELLED',
            default => strtoupper((string) $this->status),
        };
    }

    /**
     * Human-readable campaign type.
     */
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
     * Determine whether the campaign is currently within its
     * configured date range.
     */
    public function isWithinSchedule(): bool
    {
        $today = now()->startOfDay();

        if (
            $this->start_date &&
            $today->lt(
                $this->start_date->copy()->startOfDay()
            )
        ) {
            return false;
        }

        if (
            $this->end_date &&
            $today->gt(
                $this->end_date->copy()->endOfDay()
            )
        ) {
            return false;
        }

        return true;
    }
}
