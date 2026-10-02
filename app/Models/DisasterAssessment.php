<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DisasterAssessment extends Model
{
    protected $fillable = [
        'disaster_event_id',
        'household_id',

        'assessment_date',
        'assessor_id',

        'damage_level',
        'house_livability',
        'estimated_property_loss',
        'livelihood_impact',

        'affected_members',
        'household_affected',
        'evacuated',
        'evacuation_center',

        'interview_notes',
        'remarks',

        'verification_status',
        'verified_by',
        'verified_at',

        'voucher_eligible',
        'eligibility_reason',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'verified_at' => 'datetime',

        'estimated_property_loss' => 'decimal:2',

        'affected_members' => 'integer',

        'household_affected' => 'boolean',
        'evacuated' => 'boolean',

        'voucher_eligible' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Disaster Event
    |--------------------------------------------------------------------------
    */

    public function disasterEvent(): BelongsTo
    {
        return $this->belongsTo(
            DisasterEvent::class,
            'disaster_event_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Household
    |--------------------------------------------------------------------------
    */

    public function household(): BelongsTo
    {
        return $this->belongsTo(
            Household::class,
            'household_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Assessor
    |--------------------------------------------------------------------------
    */

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assessor_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verifier
    |--------------------------------------------------------------------------
    */

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relief Voucher
    |--------------------------------------------------------------------------
    */

    public function reliefVoucher(): HasOne
    {
        return $this->hasOne(
            ReliefVoucher::class,
            'disaster_assessment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Voucher Eligibility
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether this assessment is eligible
     * for relief voucher generation.
     */
    public function isVoucherEligible(): bool
    {
        return (bool) $this->voucher_eligible;
    }

    /**
     * Determine whether this assessment has already
     * been issued a relief voucher.
     */
    public function hasReliefVoucher(): bool
    {
        return $this->reliefVoucher()->exists();
    }
}
