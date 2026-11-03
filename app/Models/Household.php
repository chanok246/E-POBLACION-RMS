<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Household extends Model
{
    use HasFactory;

    protected $table = 'households';

    /**
     * Columns that always exist in the households table.
     *
     * Household classification columns are added to the fillable
     * list only when they actually exist in the database.
     */
    protected $fillable = [
        'household_number',
        'address',
        'purok',
        'latitude',
        'longitude',
        'household_head_id',
        'status',
    ];

    /**
     * Household-level classification columns.
     *
     * The household head is represented by household_head_id.
     */
    public const CLASSIFICATION_COLUMNS = [
        'is_low_income',
        'is_4ps_beneficiary',
    ];

    /**
     * Per-request cache of columns confirmed to exist.
     *
     * @var array<string, array<int, string>>
     */
    protected static array $existingColumnsCache = [];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_low_income' => 'boolean',
            'is_4ps_beneficiary' => 'boolean',
        ];
    }

    /**
     * Return the normal fillable fields plus classification fields
     * that exist in the actual database.
     *
     * @return array<int, string>
     */
    public function getFillable(): array
    {
        return array_values(array_unique(array_merge(
            $this->fillable,
            static::existingColumns(self::CLASSIFICATION_COLUMNS)
        )));
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

    /**
     * Return labels for active household classifications.
     *
     * @return array<int, string>
     */
    public function getClassificationLabelsAttribute(): array
    {
        $labels = [];

        if ($this->isLowIncome()) {
            $labels[] = 'Low-Income / Indigent';
        }

        if ($this->is4psBeneficiary()) {
            $labels[] = '4Ps Beneficiary';
        }

        return $labels;
    }

    /*
    |--------------------------------------------------------------------------
    | Classification Helpers
    |--------------------------------------------------------------------------
    */

    public function isLowIncome(): bool
    {
        return (bool) ($this->attributes['is_low_income'] ?? false);
    }

    public function is4psBeneficiary(): bool
    {
        return (bool) ($this->attributes['is_4ps_beneficiary'] ?? false);
    }

    /*
    |--------------------------------------------------------------------------
    | Classification Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLowIncome(Builder $query): Builder
    {
        return $this->applyFlagFilter($query, 'is_low_income');
    }

    public function scopeFourPsBeneficiary(Builder $query): Builder
    {
        return $this->applyFlagFilter($query, 'is_4ps_beneficiary');
    }

    protected function applyFlagFilter(
        Builder $query,
        string $column
    ): Builder {
        if (static::existingColumns([$column]) === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable() . '.' . $column, true);
    }

    /**
     * Check actual database columns without constructing another
     * Household model. Constructing one here would recursively call
     * getFillable() and could exhaust PHP memory.
     *
     * @param array<int, string> $columns
     * @return array<int, string>
     */
    protected static function existingColumns(array $columns): array
    {
        // Use the known table name directly to avoid model construction.
        $table = 'households';
        $key = $table . '|' . implode(',', $columns);

        if (! array_key_exists($key, static::$existingColumnsCache)) {
            static::$existingColumnsCache[$key] = array_values(
                array_filter(
                    $columns,
                    function (string $column) use ($table): bool {
                        try {
                            return Schema::hasColumn($table, $column);
                        } catch (\Throwable $exception) {
                            return false;
                        }
                    }
                )
            );
        }

        return static::$existingColumnsCache[$key];
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
