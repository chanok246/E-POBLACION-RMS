<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class HouseholdMember extends Model
{
    use HasFactory;

    public const STUDENT_LEVELS = [
        'Elementary',
        'High School',
        'College',
    ];

    /**
     * Optional classification fields supported when their columns exist.
     */
    public const OPTIONAL_CLASSIFICATION_COLUMNS = [
        'is_senior_citizen',
        'is_student',
        'education_level',
        'student_level',
    ];

    protected $fillable = [
        'household_id',
        'resident_id',
        'relationship',
        'is_pwd',
        'is_solo_parent',
        'is_high_school',
        'is_high_school_student',
    ];

    protected $casts = [
        'household_id' => 'integer',
        'resident_id' => 'integer',
        'is_pwd' => 'boolean',
        'is_solo_parent' => 'boolean',
        'is_high_school' => 'boolean',
        'is_high_school_student' => 'boolean',
        'is_senior_citizen' => 'boolean',
        'is_student' => 'boolean',
        'education_level' => 'string',
        'student_level' => 'string',
    ];

    /**
     * Cache the columns that exist in the current database connection.
     *
     * @var array<string, array<int, string>>
     */
    protected static array $existingColumnsCache = [];

    public function getFillable(): array
    {
        return array_values(array_unique(array_merge(
            $this->fillable,
            static::existingColumns(
                self::OPTIONAL_CLASSIFICATION_COLUMNS
            )
        )));
    }

    protected static function booted(): void
    {
        static::saving(function (self $member): void {
            $member->syncStudentClassification();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Classification Helpers
    |--------------------------------------------------------------------------
    */

    public function isPwd(): bool
    {
        return (bool) ($this->attributes['is_pwd'] ?? false);
    }

    public function isSoloParent(): bool
    {
        return (bool) ($this->attributes['is_solo_parent'] ?? false);
    }

    /**
     * Uses the stored classification flag or the resident's age.
     */
    public function isSeniorCitizen(): bool
    {
        if ((bool) ($this->attributes['is_senior_citizen'] ?? false)) {
            return true;
        }

        $age = $this->residentAge();

        return $age !== null && $age >= 60;
    }

    /**
     * A member is a student when the student flag, education level,
     * or legacy High School flags indicate that classification.
     */
    public function isStudent(): bool
    {
        return (bool) ($this->attributes['is_student'] ?? false)
            || $this->studentLevel() !== null
            || (bool) ($this->attributes['is_high_school_student'] ?? false)
            || (bool) ($this->attributes['is_high_school'] ?? false);
    }

    public function isHighSchoolStudent(): bool
    {
        return $this->studentLevel() === 'High School';
    }

    /**
     * education_level is the canonical field.
     * student_level remains supported for compatibility.
     */
    public function studentLevel(): ?string
    {
        $level = $this->normalizeStudentLevel(
            $this->attributes['education_level'] ?? null
        );

        if ($level !== null) {
            return $level;
        }

        $level = $this->normalizeStudentLevel(
            $this->attributes['student_level'] ?? null
        );

        if ($level !== null) {
            return $level;
        }

        if (
            (bool) ($this->attributes['is_high_school_student'] ?? false)
            || (bool) ($this->attributes['is_high_school'] ?? false)
        ) {
            return 'High School';
        }

        return null;
    }

    /**
     * Return all applicable individual classifications.
     *
     * Household-head status is determined by the existing household
     * relationship and household_head_id, not by another flag.
     *
     * @return array<int, string>
     */
    public function classificationLabels(): array
    {
        $labels = [];

        if ($this->isPwd()) {
            $labels[] = 'PWD';
        }

        if ($this->isSeniorCitizen()) {
            $labels[] = 'Senior Citizen';
        }

        if ($this->isSoloParent()) {
            $labels[] = 'Solo Parent';
        }

        if ($this->isStudent()) {
            $level = $this->studentLevel();

            $labels[] = $level !== null
                ? 'Student (' . $level . ')'
                : 'Student';
        }

        return $labels;
    }

    /*
    |--------------------------------------------------------------------------
    | Classification Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePwd(Builder $query): Builder
    {
        return $this->applyFlagFilter($query, ['is_pwd']);
    }

    public function scopeSoloParent(Builder $query): Builder
    {
        return $this->applyFlagFilter($query, ['is_solo_parent']);
    }

    public function scopeStudent(Builder $query): Builder
    {
        return $this->applyFlagFilter(
            $query,
            [
                'is_student',
                'is_high_school_student',
                'is_high_school',
            ],
            [
                'education_level',
                'student_level',
            ]
        );
    }

    /**
     * Filter by education level while supporting legacy High School flags.
     */
    public function scopeStudentLevel(
        Builder $query,
        string $level
    ): Builder {
        $normalized = $this->normalizeStudentLevel($level);

        if ($normalized === null) {
            return $query->whereRaw('1 = 0');
        }

        $table = 'household_members';

        $levelColumns = static::existingColumns([
            'education_level',
            'student_level',
        ]);

        $legacyColumns = $normalized === 'High School'
            ? static::existingColumns([
                'is_high_school_student',
                'is_high_school',
            ])
            : [];

        if ($levelColumns === [] && $legacyColumns === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $nested) use (
            $table,
            $levelColumns,
            $legacyColumns,
            $normalized
        ) {
            foreach ($levelColumns as $column) {
                $nested->orWhere(
                    $table . '.' . $column,
                    $normalized
                );
            }

            foreach ($legacyColumns as $column) {
                $nested->orWhere($table . '.' . $column, true);
            }
        });
    }

    /**
     * Senior citizens are identified by the stored flag or resident age.
     */
    public function scopeSeniorCitizen(Builder $query): Builder
    {
        $flags = static::existingColumns(['is_senior_citizen']);

        $hasBirthDate = static::existingColumnsOn(
            'residents',
            ['birth_date']
        ) !== [];

        if ($flags === [] && ! $hasBirthDate) {
            return $query->whereRaw('1 = 0');
        }

        $cutoff = Carbon::now()->subYears(60)->toDateString();

        return $query->where(function (Builder $nested) use (
            $flags,
            $hasBirthDate,
            $cutoff
        ) {
            foreach ($flags as $column) {
                $nested->orWhere(
                    'household_members.' . $column,
                    true
                );
            }

            if ($hasBirthDate) {
                $nested->orWhereHas(
                    'resident',
                    function (Builder $resident) use ($cutoff) {
                        $resident->whereDate(
                            'birth_date',
                            '<=',
                            $cutoff
                        );
                    }
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Filter by any true flag or any populated classification column.
     *
     * @param array<int, string> $flagColumns
     * @param array<int, string> $notNullColumns
     */
    protected function applyFlagFilter(
        Builder $query,
        array $flagColumns,
        array $notNullColumns = []
    ): Builder {
        $flags = static::existingColumns($flagColumns);
        $notNull = static::existingColumns($notNullColumns);

        if ($flags === [] && $notNull === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $nested) use (
            $flags,
            $notNull
        ) {
            foreach ($flags as $column) {
                $nested->orWhere(
                    'household_members.' . $column,
                    true
                );
            }

            foreach ($notNull as $column) {
                $nested->orWhereNotNull(
                    'household_members.' . $column
                );
            }
        });
    }

    protected function residentAge(): ?int
    {
        $resident = $this->resident;

        if (! $resident) {
            return null;
        }

        $birthDate = $resident->getAttributes()['birth_date'] ?? null;

        if (empty($birthDate)) {
            return null;
        }

        try {
            return Carbon::parse($birthDate)->age;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function normalizeStudentLevel(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $key = strtolower(
            preg_replace('/[^a-z]/i', '', (string) $value) ?? ''
        );

        return match ($key) {
            'elementary' => 'Elementary',
            'highschool' => 'High School',
            'college' => 'College',
            default => null,
        };
    }

    /**
     * Synchronize student classification fields on save.
     *
     * education_level is preferred when present. student_level is
     * maintained too when that legacy column exists.
     *
     * The old High School flags remain synchronized for compatibility.
     */
    protected function syncStudentClassification(): void
    {
        $columns = static::existingColumns([
            'is_student',
            'education_level',
            'student_level',
            'is_high_school',
            'is_high_school_student',
        ]);

        if ($columns === []) {
            return;
        }

        $has = fn (string $column): bool => in_array(
            $column,
            $columns,
            true
        );

        $levelColumns = array_values(array_filter(
            ['education_level', 'student_level'],
            $has
        ));

        $hsColumns = array_values(array_filter(
            ['is_high_school', 'is_high_school_student'],
            $has
        ));

        $hsNow = false;
        $hsDirtyTrue = false;
        $hsDirtyAny = false;

        foreach ($hsColumns as $column) {
            $value = (bool) ($this->attributes[$column] ?? false);

            $hsNow = $hsNow || $value;

            if ($this->isDirty($column)) {
                $hsDirtyAny = true;

                if ($value) {
                    $hsDirtyTrue = true;
                }
            }
        }

        $level = null;
        $levelDirty = false;

        // If both level fields exist, honor whichever field was changed.
        foreach ($levelColumns as $column) {
            if ($this->isDirty($column)) {
                $level = $this->normalizeStudentLevel(
                    $this->attributes[$column] ?? null
                );

                $levelDirty = true;
                break;
            }
        }

        // Otherwise prefer education_level, then legacy student_level.
        if (! $levelDirty) {
            foreach ($levelColumns as $column) {
                $level = $this->normalizeStudentLevel(
                    $this->attributes[$column] ?? null
                );

                if ($level !== null) {
                    break;
                }
            }
        }

        $studentOff = $has('is_student')
            && $this->isDirty('is_student')
            && array_key_exists('is_student', $this->attributes)
            && ! (bool) $this->attributes['is_student']
            && ! $hsDirtyTrue
            && ! ($levelDirty && $level !== null);

        if ($studentOff) {
            $level = null;
            $hsNow = false;
            $student = false;
        } else {
            if ($hsDirtyTrue) {
                $hsNow = true;
                $level = 'High School';
            } elseif ($levelDirty) {
                $hsNow = $level === 'High School';
            } elseif ($hsDirtyAny && ! $hsNow && $level === 'High School') {
                $level = null;
            } elseif ($hsNow && $level === null) {
                $level = 'High School';
            }

            $student = (bool) ($this->attributes['is_student'] ?? false)
                || $level !== null
                || $hsNow;
        }

        foreach ($levelColumns as $column) {
            $this->setAttribute($column, $level);
        }

        foreach ($hsColumns as $column) {
            $this->setAttribute($column, $hsNow);
        }

        if ($has('is_student')) {
            $this->setAttribute('is_student', $student);
        }
    }

    /**
     * Return only requested columns present in household_members.
     *
     * Do not instantiate this model here: getFillable() calls this method
     * and model instantiation could cause recursive calls and memory errors.
     *
     * @param array<int, string> $columns
     * @return array<int, string>
     */
    protected static function existingColumns(array $columns): array
    {
        return static::existingColumnsOn('household_members', $columns);
    }

    /**
     * Return only requested columns present in a specified table.
     *
     * @param array<int, string> $columns
     * @return array<int, string>
     */
    protected static function existingColumnsOn(
        string $table,
        array $columns
    ): array {
        $key = $table . '|' . implode(',', $columns);

        if (! isset(static::$existingColumnsCache[$key])) {
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
}
