<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasFactory;

    protected $table = 'households';

    protected $fillable = [
        'household_number',
        'address',
        'purok',
        'latitude',
        'longitude',
        'household_head_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function householdHead(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class,
            'household_head_id'
        );
    }

    public function members(): HasMany
    {
        return $this->hasMany(
            HouseholdMember::class,
            'household_id'
        );
    }

    /**
     * Relief vouchers issued to this household.
     */
    public function reliefVouchers(): HasMany
    {
        return $this->hasMany(
            ReliefVoucher::class,
            'household_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    public function getHouseholdNameAttribute(): string
    {
        if ($this->householdHead) {
            return $this->householdHead->full_name . ' Household';
        }

        return 'Household ' . $this->household_number;
    }

    public function getFullAddressAttribute(): string
    {
        return trim(
            collect([
                $this->address,
                $this->purok,
            ])
                ->filter()
                ->implode(', ')
        );
    }

    public function getPurokNameAttribute(): string
    {
        return $this->purok ?: 'Unassigned';
    }

    public function getMapCoordinatesAttribute(): ?array
    {
        if (! $this->hasMapCoordinates()) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Map Helpers
    |--------------------------------------------------------------------------
    */

    public function hasMapCoordinates(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }

    public function isMapped(): bool
    {
        return $this->hasMapCoordinates();
    }
}
