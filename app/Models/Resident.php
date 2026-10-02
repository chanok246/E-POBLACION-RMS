<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    |
    | These are the fields that can be saved using:
    |
    | Resident::create()
    | $resident->update()
    |
    | Age is NOT stored in the database.
    | It is automatically calculated from birth_date.
    |
    */

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',

        // Basic information
        'birth_date',
        'sex',
        'civil_status',

        // Contact information
        'contact_number',
        'email',

        // Address
        'address',

        // Residency information
        'residency_type',
        'residency_start_year',
        'residency_year_estimated',

        // Primary occupation / status
        'occupation',

        // Resident record status
        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTE CASTING
    |--------------------------------------------------------------------------
    |
    | birth_date:
    |     Stored as a Carbon date instance.
    |
    | residency_start_year:
    |     Stored as an integer.
    |
    | residency_year_estimated:
    |     Stored as true / false.
    |
    */

    protected $casts = [
        'birth_date' => 'date',
        'residency_start_year' => 'integer',
        'residency_year_estimated' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | FULL NAME
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | Juan Dela Cruz
    | Juan P. Dela Cruz
    | Juan Dela Cruz Jr.
    |
    */

    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' .
            ($this->middle_name ? $this->middle_name . ' ' : '') .
            $this->last_name .
            ($this->suffix ? ' ' . $this->suffix : '')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AUTOMATIC AGE
    |--------------------------------------------------------------------------
    |
    | Age is calculated from birth_date.
    | It is NOT stored in the database.
    |
    */

    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return Carbon::parse($this->birth_date)->age;
    }


    /*
    |--------------------------------------------------------------------------
    | AUTOMATIC YEARS OF RESIDENCY
    |--------------------------------------------------------------------------
    |
    | Years of residency is calculated from the individual resident's
    | residency_start_year.
    |
    | Example:
    |
    | Current year:          2026
    | Residency start year: 2000
    | Years of residency:   26 years
    |
    | This value is NOT stored in the database.
    | It is calculated automatically whenever it is accessed.
    |
    */

    public function getYearsOfResidencyAttribute(): ?int
    {
        if (!$this->residency_start_year) {
            return null;
        }

        $currentYear = now()->year;

        $years = $currentYear - $this->residency_start_year;

        /*
        |----------------------------------------------------------------------
        | Prevent invalid negative values
        |----------------------------------------------------------------------
        |
        | This protects the calculation if an incorrect future year is
        | accidentally entered.
        |
        */

        return max(0, $years);
    }


    /*
    |--------------------------------------------------------------------------
    | HOUSEHOLD MEMBERSHIP
    |--------------------------------------------------------------------------
    |
    | residents.id
    |      ↓
    | household_members.resident_id
    |
    | One resident may have household membership records.
    |
    */

    public function householdMemberships(): HasMany
    {
        return $this->hasMany(
            HouseholdMember::class,
            'resident_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HOUSEHOLDS WHERE RESIDENT IS HEAD
    |--------------------------------------------------------------------------
    |
    | households.household_head_id
    |      ↓
    | residents.id
    |
    | A resident can be the head of one or more household records.
    |
    */

    public function headedHouseholds(): HasMany
    {
        return $this->hasMany(
            Household::class,
            'household_head_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT REQUESTS
    |--------------------------------------------------------------------------
    |
    | One resident can have many document requests.
    |
    */

    public function documentRequests(): HasMany
    {
        return $this->hasMany(
            DocumentRequest::class,
            'resident_id'
        );
    }
}
