<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyHotline extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'emergency_hotlines';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'category',
        'contact_person',
        'phone_number',
        'alternate_number',
        'address',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * User who created this hotline record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /**
     * User who last updated this hotline record.
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
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the hotline is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }


    /**
     * Determine whether the hotline is currently inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }


    /*
    |--------------------------------------------------------------------------
    | Category Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Return the standardized category list used by the system.
     */
    public static function categories(): array
    {
        return [
            'Barangay',
            'Police',
            'Fire',
            'Medical / Ambulance',
            'Disaster / Emergency',
            'Rescue',
            'Other',
        ];
    }


    /**
     * Determine whether this hotline belongs to the Fire category.
     */
    public function isFire(): bool
    {
        return $this->category === 'Fire';
    }


    /**
     * Determine whether this hotline belongs to the Police category.
     */
    public function isPolice(): bool
    {
        return $this->category === 'Police';
    }


    /**
     * Determine whether this hotline belongs to the Medical / Ambulance category.
     */
    public function isMedical(): bool
    {
        return $this->category === 'Medical / Ambulance';
    }


    /**
     * Determine whether this hotline belongs to the Disaster / Emergency category.
     */
    public function isDisasterEmergency(): bool
    {
        return $this->category === 'Disaster / Emergency';
    }


    /**
     * Determine whether this hotline belongs to the Rescue category.
     */
    public function isRescue(): bool
    {
        return $this->category === 'Rescue';
    }


    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get a readable category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'Barangay' =>
                'Barangay',

            'Police' =>
                'Police',

            'Fire' =>
                'Fire / BFP',

            'Medical / Ambulance' =>
                'Medical / Ambulance',

            'Disaster / Emergency' =>
                'Disaster / Emergency',

            'Rescue' =>
                'Rescue',

            'Other' =>
                'Other',

            default =>
                $this->category ?: 'Uncategorized',
        };
    }


    /**
     * Get a readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' =>
                'Active',

            'inactive' =>
                'Inactive',

            default =>
                ucfirst((string) $this->status),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Status Badge Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Return a Bootstrap badge class appropriate for the hotline status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' =>
                'text-bg-success',

            'inactive' =>
                'text-bg-secondary',

            default =>
                'text-bg-secondary',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Category Icon Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Return a Bootstrap Icons class appropriate for the category.
     */
    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'Barangay' =>
                'bi-building',

            'Police' =>
                'bi-shield-fill-check',

            'Fire' =>
                'bi-fire',

            'Medical / Ambulance' =>
                'bi-heart-pulse-fill',

            'Disaster / Emergency' =>
                'bi-exclamation-triangle-fill',

            'Rescue' =>
                'bi-life-preserver',

            'Other' =>
                'bi-telephone-fill',

            default =>
                'bi-telephone-fill',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Phone Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether an alternate number exists.
     */
    public function hasAlternateNumber(): bool
    {
        return filled($this->alternate_number);
    }


    /**
     * Return the primary phone number.
     */
    public function getPrimaryPhoneAttribute(): string
    {
        return (string) $this->phone_number;
    }


    /**
     * Return the alternate phone number.
     */
    public function getAlternatePhoneAttribute(): ?string
    {
        return filled($this->alternate_number)
            ? (string) $this->alternate_number
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Operational Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether this hotline can currently be presented
     * as an available emergency contact.
     */
    public function isAvailableForEmergency(): bool
    {
        return $this->isActive()
            && filled($this->phone_number);
    }


    /**
     * Return the best available phone number.
     */
    public function getBestContactNumberAttribute(): string
    {
        return filled($this->phone_number)
            ? (string) $this->phone_number
            : (
                filled($this->alternate_number)
                    ? (string) $this->alternate_number
                    : ''
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to active hotlines only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }


    /**
     * Scope to inactive hotlines only.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }


    /**
     * Scope to a specific category.
     */
    public function scopeCategory(
        Builder $query,
        string $category
    ): Builder {
        return $query->where('category', $category);
    }


    /**
     * Scope to hotlines that can currently be contacted.
     */
    public function scopeAvailableForEmergency(
        Builder $query
    ): Builder {
        return $query
            ->where('status', 'active')
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '');
    }
}