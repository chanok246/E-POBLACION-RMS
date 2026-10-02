<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterEvent extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    |
    | These fields match the current disaster_events database structure.
    |
    */

    protected $fillable = [
        'event_code',
        'name',
        'type',
        'description',
        'start_date',
        'end_date',
        'status',
        'assistance_type',
        'assistance_amount',
        'barangay',
        'municipality',
        'province',
        'created_by',
        'updated_by',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assistance_amount' => 'decimal:2',
    ];


    /*
    |--------------------------------------------------------------------------
    | Disaster Assessments
    |--------------------------------------------------------------------------
    |
    | One disaster event can have many household assessments.
    |
    */

    public function assessments(): HasMany
    {
        return $this->hasMany(
            DisasterAssessment::class,
            'disaster_event_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Active Event
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'active';
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Assessment Count
    |--------------------------------------------------------------------------
    */

    public function getAssessmentCountAttribute(): int
    {
        return $this->assessments()->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Verified Assessment Count
    |--------------------------------------------------------------------------
    */

    public function getVerifiedAssessmentCountAttribute(): int
    {
        return $this->assessments()
            ->where(
                'verification_status',
                'Verified'
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Voucher Eligible Count
    |--------------------------------------------------------------------------
    */

    public function getVoucherEligibleCountAttribute(): int
    {
        return $this->assessments()
            ->where(
                'verification_status',
                'Verified'
            )
            ->where(
                'voucher_eligible',
                true
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Closed Event
    |--------------------------------------------------------------------------
    */

    public function isClosed(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'closed';
    }


    /*
    |--------------------------------------------------------------------------
    | Helper: Cancelled Event
    |--------------------------------------------------------------------------
    */

    public function isCancelled(): bool
    {
        return strtolower(
            (string) $this->status
        ) === 'cancelled';
    }
}
