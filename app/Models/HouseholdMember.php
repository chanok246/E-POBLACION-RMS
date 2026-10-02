<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'resident_id',
        'relationship',
        'is_pwd',
        'is_solo_parent',
        'is_high_school',
    ];

    protected $casts = [
        'is_pwd' => 'boolean',
        'is_solo_parent' => 'boolean',
        'is_high_school' => 'boolean',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(
            Household::class,
            'household_id'
        );
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class,
            'resident_id'
        );
    }
}