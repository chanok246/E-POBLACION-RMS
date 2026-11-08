<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Resident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    private const STATUS_KEYWORDS = [
        'pwd' => ['pwd', 'person with disability', 'persons with disability', 'disability', 'disabled'],
        'senior' => ['senior', 'senior citizen', 'senior citizens', 'elderly'],
        'solo_parent' => ['solo parent', 'solo parents', 'solo', 'single parent'],
        'student' => ['student', 'students'],
        'elementary' => ['elementary'],
        'high_school' => ['high school', 'highschool', 'hs'],
        'college' => ['college'],
    ];

    /**
     * Residents listing:
     * - Name search only.
     * - Purok filtering through the existing household relationship.
     * - Separate classification, sex, and record-status filters.
     * - Whitelisted sorting options.
     */
    public function index(Request $request)
    {
        $query = Resident::query();

        /*
        |--------------------------------------------------------------------------
        | Name-only search
        |--------------------------------------------------------------------------
        |
        | Address, household number, street, and Purok are deliberately
        | excluded from the search conditions.
        |
        */
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->whereRaw(
                "CONCAT_WS(' ',
                    NULLIF(first_name, ''),
                    NULLIF(middle_name, ''),
                    NULLIF(last_name, ''),
                    NULLIF(suffix, '')
                ) LIKE ?",
                ['%' . $search . '%']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Purok filter
        |--------------------------------------------------------------------------
        |
        | A resident is included when their household_members record
        | points to a household with the selected Purok.
        |
        */
        if ($request->filled('purok')) {
            $purok = trim((string) $request->input('purok'));
            $residentsTable = (new Resident())->getTable();

            $query->whereExists(function ($subQuery) use (
                $purok,
                $residentsTable
            ) {
                $subQuery->selectRaw('1')
                    ->from('household_members')
                    ->join(
                        'households',
                        'households.id',
                        '=',
                        'household_members.household_id'
                    )
                    ->whereColumn(
                        'household_members.resident_id',
                        $residentsTable . '.id'
                    )
                    ->where('households.purok', $purok);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Individual classification filter
        |--------------------------------------------------------------------------
        |
        | Classification searching is separate from the name search.
        | Supported values include PWD, Senior Citizen, Solo Parent,
        | Student, Elementary, High School, and College.
        |
        */
        if ($request->filled('classification')) {
            $classification = trim(
                (string) $request->input('classification')
            );

            $classificationKeys = $this->detectStatusKeywords(
                $classification
            );

            if ($classificationKeys !== []) {
                $query->where(function (Builder $q) use (
                    $classificationKeys
                ) {
                    foreach ($classificationKeys as $key) {
                        $this->applyStatusSearch($q, $key);
                    }
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sex filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('sex')) {
            $query->where(
                'sex',
                $request->input('sex')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Record status filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

               /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        |
        | Primary sorting: Last Name / Surname.
        | Secondary sorting: First Name.
        | Tertiary sorting: Middle Name, Suffix, and ID.
        |
        */
        $allowedSorts = [
            'name_asc',
            'name_desc',
            'newest',
            'oldest',
        ];

        $sort = (string) $request->input('sort', 'newest');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        switch ($sort) {
            case 'name_asc':
                $query->orderByRaw("LOWER(COALESCE(last_name, '')) ASC")
                    ->orderByRaw("LOWER(COALESCE(first_name, '')) ASC")
                    ->orderByRaw("LOWER(COALESCE(middle_name, '')) ASC")
                    ->orderByRaw("LOWER(COALESCE(suffix, '')) ASC")
                    ->orderBy('id', 'asc');
                break;

            case 'name_desc':
                $query->orderByRaw("LOWER(COALESCE(last_name, '')) DESC")
                    ->orderByRaw("LOWER(COALESCE(first_name, '')) ASC")
                    ->orderByRaw("LOWER(COALESCE(middle_name, '')) ASC")
                    ->orderByRaw("LOWER(COALESCE(suffix, '')) ASC")
                    ->orderBy('id', 'asc');
                break;

            case 'oldest':
                $query->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
                break;

            case 'newest':
            default:
                $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;
        }

        $residents = $query
            ->paginate(10)
            ->withQueryString();

        $statusLabels = $this->buildStatusLabels($residents);

        /*
         * Only existing Purok values are offered in the filter.
         * No new records or database columns are created.
         */
        $puroks = Household::query()
            ->whereNotNull('purok')
            ->where('purok', '!=', '')
            ->distinct()
            ->orderBy('purok')
            ->pluck('purok');

        $selectedPurok = $request->input('purok', '');

        return view('residents.index', compact(
            'residents',
            'statusLabels',
            'puroks',
            'selectedPurok',
            'sort'
        ));
    }

    public function create()
    {
        $this->authorizeResidentManagement();

        return view('residents.create');
    }

    public function store(Request $request)
    {
        $this->authorizeResidentManagement();
        $this->normalizeOptionalContactFields($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['required', 'date', 'before:today'],
            'sex' => ['required', Rule::in(['Male', 'Female'])],
            'civil_status' => ['required', 'string', 'max:100'],
            'contact_number' => ['nullable', $this->contactNumberRule()],
            'email' => ['nullable', 'max:255', $this->emailRule()],
            'address' => ['nullable', 'string', 'max:1000'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'residency_start_year' => [
                'nullable',
                'integer',
                'between:1900,' . date('Y'),
            ],
            'status' => ['nullable', Rule::in(['Active', 'Archived'])],
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'birth_date.required' => 'Birth date is required.',
            'birth_date.before' => 'Birth date must be before today.',
            'residency_start_year.integer' => 'Residency start year must be a valid year.',
            'residency_start_year.between' => 'Residency start year must be between 1900 and the current year.',
        ]);

        $residencyError = $this->validateResidencyStartYear($validated);

        if ($residencyError !== null) {
            return back()
                ->withInput()
                ->withErrors(['residency_start_year' => $residencyError]);
        }

        $duplicate = Resident::query()
            ->whereRaw(
                'LOWER(first_name) = ?',
                [strtolower(trim($validated['first_name']))]
            )
            ->whereRaw(
                'LOWER(last_name) = ?',
                [strtolower(trim($validated['last_name']))]
            )
            ->whereDate('birth_date', $validated['birth_date'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'duplicate' => 'This resident already exists in the records.',
                ]);
        }

        $validated['first_name'] = trim($validated['first_name']);
        $validated['last_name'] = trim($validated['last_name']);

        $validated['middle_name'] = isset($validated['middle_name'])
            ? trim($validated['middle_name'])
            : null;

        foreach (['contact_number', 'email', 'address', 'occupation'] as $field) {
            $validated[$field] = filled($validated[$field] ?? null)
                ? trim((string) $validated[$field])
                : null;
        }

        $validated['status'] = $validated['status'] ?? 'Active';

        Resident::create($validated);

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident successfully added.');
    }

    public function show(Resident $resident)
    {
        return view('residents.show', compact('resident'));
    }

    /**
     * Legacy placeholders in address/occupation are cleared on this
     * in-memory model only. Opening the edit form does not alter the DB.
     */
    public function edit(Resident $resident)
    {
        $this->authorizeResidentManagement();

        foreach (['address', 'occupation'] as $field) {
            if ($this->isOptionalContactPlaceholder(
                $resident->getAttribute($field)
            )) {
                $resident->setAttribute($field, null);
            }
        }

        $householdMember = HouseholdMember::query()
            ->where('resident_id', $resident->id)
            ->first();

        return view(
            'residents.edit',
            compact('resident', 'householdMember')
        );
    }

    /**
     * Update personal information and individual classifications.
     * Household-level classifications remain managed through Household.
     */
    public function update(Request $request, Resident $resident)
    {
        $this->authorizeResidentManagement();
        $this->normalizeOptionalContactFields($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['required', 'date', 'before:today'],
            'sex' => ['required', Rule::in(['Male', 'Female'])],
            'civil_status' => ['required', 'string', 'max:100'],
            'contact_number' => ['nullable', $this->contactNumberRule()],
            'email' => ['nullable', 'max:255', $this->emailRule()],
            'address' => ['nullable', 'string', 'max:1000'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'residency_start_year' => [
                'nullable',
                'integer',
                'between:1900,' . date('Y'),
            ],
            'status' => ['nullable', Rule::in(['Active', 'Archived'])],
            'is_pwd' => ['sometimes', 'nullable', 'boolean'],
            'is_solo_parent' => ['sometimes', 'nullable', 'boolean'],
            'is_senior_citizen' => ['sometimes', 'nullable', 'boolean'],
            'is_student' => ['sometimes', 'nullable', 'boolean'],
            'education_level' => [
                'sometimes',
                'nullable',
                Rule::in(['Elementary', 'High School', 'College']),
            ],
        ], [
            'birth_date.before' => 'Birth date must be before today.',
            'residency_start_year.integer' => 'Residency start year must be a valid year.',
            'residency_start_year.between' => 'Residency start year must be between 1900 and the current year.',
            'education_level.in' => 'Select Elementary, High School, or College for the education level.',
        ]);

        $residencyError = $this->validateResidencyStartYear($validated);

        if ($residencyError !== null) {
            return back()
                ->withInput()
                ->withErrors(['residency_start_year' => $residencyError]);
        }

        $duplicate = Resident::query()
            ->whereRaw(
                'LOWER(first_name) = ?',
                [strtolower(trim($validated['first_name']))]
            )
            ->whereRaw(
                'LOWER(last_name) = ?',
                [strtolower(trim($validated['last_name']))]
            )
            ->whereDate('birth_date', $validated['birth_date'])
            ->where('id', '!=', $resident->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'duplicate' => 'Another resident with the same name and birth date already exists.',
                ]);
        }

        $classificationKeys = [
            'is_pwd',
            'is_solo_parent',
            'is_senior_citizen',
            'is_student',
            'education_level',
        ];

        $classificationChanges = array_intersect_key(
            $validated,
            array_flip($classificationKeys)
        );

        $residentChanges = array_diff_key(
            $validated,
            array_flip($classificationKeys)
        );

        $residentChanges['first_name'] = trim(
            $residentChanges['first_name']
        );

        $residentChanges['last_name'] = trim(
            $residentChanges['last_name']
        );

        if (array_key_exists('middle_name', $residentChanges)) {
            $residentChanges['middle_name'] = filled(
                $residentChanges['middle_name']
            )
                ? trim($residentChanges['middle_name'])
                : null;
        }

        foreach (['contact_number', 'email', 'address', 'occupation'] as $field) {
            if (array_key_exists($field, $residentChanges)) {
                $residentChanges[$field] = filled($residentChanges[$field])
                    ? trim((string) $residentChanges[$field])
                    : null;
            }
        }

        $householdMember = null;

        if ($classificationChanges !== []) {
            $householdMember = HouseholdMember::query()
                ->where('resident_id', $resident->id)
                ->first();

            if (! $householdMember) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'classification' => 'This resident is not linked to a household member record. Link the resident to the correct household before updating individual classifications.',
                    ]);
            }

            $availableClassificationColumns = array_values(array_filter(
                $classificationKeys,
                fn (string $column): bool => Schema::hasColumn(
                    'household_members',
                    $column
                )
            ));

            $classificationChanges = array_intersect_key(
                $classificationChanges,
                array_flip($availableClassificationColumns)
            );

            foreach ([
                'is_pwd',
                'is_solo_parent',
                'is_senior_citizen',
                'is_student',
            ] as $flag) {
                if (array_key_exists($flag, $classificationChanges)) {
                    $classificationChanges[$flag] = (bool) $classificationChanges[$flag];
                }
            }
        }

        DB::transaction(function () use (
            $resident,
            $residentChanges,
            $householdMember,
            $classificationChanges
        ): void {
            $resident->update($residentChanges);

            if ($householdMember && $classificationChanges !== []) {
                $householdMember
                    ->fill($classificationChanges)
                    ->save();
            }
        });

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident successfully updated.');
    }

    public function destroy(Resident $resident)
    {
        $this->authorizeResidentManagement();
        $resident->delete();

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident successfully deleted.');
    }

    /**
     * Identify classification keywords.
     */
    private function detectStatusKeywords(string $search): array
    {
        $term = strtolower(
            trim(preg_replace('/\s+/', ' ', $search))
        );

        if ($term === '') {
            return [];
        }

        $found = [];

        foreach (self::STATUS_KEYWORDS as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (
                    $term === $alias
                    || (
                        strlen($term) >= 3
                        && str_starts_with($alias, $term)
                    )
                ) {
                    $found[] = $key;
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * Apply a classification condition to a resident query.
     * Only existing household_members columns are used.
     */
    private function applyStatusSearch(Builder $q, string $key): void
    {
        $table = 'household_members';

        $has = static fn (string $column): bool => Schema::hasColumn(
            $table,
            $column
        );

        switch ($key) {
            case 'pwd':
                if ($has('is_pwd')) {
                    $q->orWhereExists(
                        $this->memberExists(
                            fn ($m) => $m->where("{$table}.is_pwd", true)
                        )
                    );
                }
                break;

            case 'senior':
                $q->orWhereDate(
                    'birth_date',
                    '<=',
                    now()->subYears(60)->toDateString()
                );

                if ($has('is_senior_citizen')) {
                    $q->orWhereExists(
                        $this->memberExists(
                            fn ($m) => $m->where(
                                "{$table}.is_senior_citizen",
                                true
                            )
                        )
                    );
                }
                break;

            case 'solo_parent':
                if ($has('is_solo_parent')) {
                    $q->orWhereExists(
                        $this->memberExists(
                            fn ($m) => $m->where(
                                "{$table}.is_solo_parent",
                                true
                            )
                        )
                    );
                }
                break;

            case 'student':
                $flagColumns = array_values(array_filter(
                    [
                        'is_student',
                        'is_high_school_student',
                        'is_high_school',
                    ],
                    $has
                ));

                $levelColumns = array_values(array_filter(
                    ['student_level', 'education_level'],
                    $has
                ));

                if ($flagColumns === [] && $levelColumns === []) {
                    break;
                }

                $q->orWhereExists(
                    $this->memberExists(
                        function ($m) use (
                            $table,
                            $flagColumns,
                            $levelColumns
                        ) {
                            foreach ($flagColumns as $column) {
                                $m->orWhere("{$table}.{$column}", true);
                            }

                            if ($flagColumns === []) {
                                foreach ($levelColumns as $column) {
                                    $m->orWhere(function ($w) use (
                                        $table,
                                        $column
                                    ) {
                                        $w->whereNotNull(
                                            "{$table}.{$column}"
                                        )->where(
                                            "{$table}.{$column}",
                                            '!=',
                                            ''
                                        );
                                    });
                                }
                            } elseif (
                                $levelColumns !== []
                                && in_array(
                                    'student_level',
                                    $levelColumns,
                                    true
                                )
                            ) {
                                $m->orWhere(function ($w) use ($table) {
                                    $w->whereNotNull(
                                        "{$table}.student_level"
                                    )->where(
                                        "{$table}.student_level",
                                        '!=',
                                        ''
                                    );
                                });
                            }
                        }
                    )
                );
                break;

            case 'elementary':
            case 'high_school':
            case 'college':
                $label = [
                    'elementary' => 'Elementary',
                    'high_school' => 'High School',
                    'college' => 'College',
                ][$key];

                $levelColumns = array_values(array_filter(
                    ['student_level', 'education_level'],
                    $has
                ));

                $legacyHighSchool = $key === 'high_school'
                    ? array_values(array_filter(
                        ['is_high_school_student', 'is_high_school'],
                        $has
                    ))
                    : [];

                if ($levelColumns === [] && $legacyHighSchool === []) {
                    break;
                }

                $q->orWhereExists(
                    $this->memberExists(
                        function ($m) use (
                            $table,
                            $label,
                            $levelColumns,
                            $legacyHighSchool,
                            $has
                        ) {
                            foreach ($levelColumns as $column) {
                                $m->orWhere(function ($w) use (
                                    $table,
                                    $column,
                                    $label,
                                    $has
                                ) {
                                    $w->where(
                                        "{$table}.{$column}",
                                        'like',
                                        "%{$label}%"
                                    );

                                    if (
                                        $column === 'education_level'
                                        && $has('is_student')
                                    ) {
                                        $w->where(
                                            "{$table}.is_student",
                                            true
                                        );
                                    }
                                });
                            }

                            foreach ($legacyHighSchool as $column) {
                                $m->orWhere("{$table}.{$column}", true);
                            }
                        }
                    )
                );
                break;
        }
    }

    /**
     * Sub-query: this resident has a household_members row matching
     * the supplied condition.
     */
    private function memberExists(\Closure $constraint): \Closure
    {
        $residentsTable = (new Resident())->getTable();

        return function ($query) use (
            $constraint,
            $residentsTable
        ) {
            $query->select(DB::raw(1))
                ->from('household_members')
                ->whereColumn(
                    'household_members.resident_id',
                    $residentsTable . '.id'
                )
                ->where($constraint);
        };
    }

    /**
     * Build classification labels for the current page.
     *
     * @return array<int, array<int, string>>
     */
    private function buildStatusLabels(
        LengthAwarePaginator $residents
    ): array {
        $items = $residents->items();

        if ($items === []) {
            return [];
        }

        $ids = collect($items)->pluck('id')->all();

        $members = HouseholdMember::query()
            ->whereIn('resident_id', $ids)
            ->get()
            ->keyBy('resident_id');

        $flag = static function (
            array $attributes,
            string $column
        ): bool {
            if (! array_key_exists($column, $attributes)) {
                return false;
            }

            return filter_var(
                $attributes[$column],
                FILTER_VALIDATE_BOOLEAN
            ) || (string) $attributes[$column] === '1';
        };

        $labels = [];

        foreach ($items as $resident) {
            $member = $members->get($resident->id);
            $attributes = $member ? $member->getAttributes() : [];
            $row = [];

            if ($member && $flag($attributes, 'is_pwd')) {
                $row[] = 'PWD';
            }

            $age = $resident->birth_date
                ? \Carbon\Carbon::parse($resident->birth_date)->age
                : null;

            if (
                ($member && $flag($attributes, 'is_senior_citizen'))
                || ($age !== null && $age >= 60)
            ) {
                $row[] = 'Senior Citizen';
            }

            if ($member && $flag($attributes, 'is_solo_parent')) {
                $row[] = 'Solo Parent';
            }

            if ($member) {
                $isStudent = method_exists($member, 'isStudent')
                    ? (bool) $member->isStudent()
                    : $flag($attributes, 'is_student');

                if ($isStudent) {
                    $level = method_exists($member, 'studentLevel')
                        ? $member->studentLevel()
                        : null;

                    $row[] = $level
                        ? 'Student (' . $level . ')'
                        : 'Student';
                }
            }

            $labels[$resident->id] = $row;
        }

        return $labels;
    }

    /**
     * Normalize optional contact fields.
     */
    private function normalizeOptionalContactFields(
        Request $request
    ): void {
        foreach ([
            'contact_number',
            'email',
            'address',
            'occupation',
        ] as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $value = $request->input($field);

            if (! is_string($value)) {
                $request->merge([$field => null]);
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                $request->merge([$field => null]);
                continue;
            }

            if ($this->isOptionalContactPlaceholder($value)) {
                if (in_array($field, ['contact_number', 'email'], true)) {
                    $request->merge([
                        $field => $this->canonicalPlaceholder($value),
                    ]);
                } else {
                    $request->merge([$field => null]);
                }

                continue;
            }

            $request->merge([$field => $value]);
        }
    }

    private function isOptionalContactPlaceholder(
        mixed $value
    ): bool {
        if (! is_string($value)) {
            return $value === null;
        }

        return in_array(
            strtolower(trim($value)),
            [
                'n/a',
                'n.a.',
                'n.a',
                'na',
                'none',
                'null',
                'not applicable',
            ],
            true
        );
    }

    private function canonicalPlaceholder(string $value): string
    {
        return strtolower(trim($value)) === 'none'
            ? 'None'
            : 'N/A';
    }

    private function contactNumberRule(): \Closure
    {
        return function (
            string $attribute,
            mixed $value,
            \Closure $fail
        ): void {
            if ($this->isOptionalContactPlaceholder($value)) {
                return;
            }

            if (
                ! is_string($value)
                || ! preg_match('/^(09|\+639)\d{9}$/', $value)
            ) {
                $fail(
                    'Enter a valid Philippine mobile number, for example 09171234567. Leave blank or enter N/A or None if there is no contact number.'
                );
            }
        };
    }

    private function emailRule(): \Closure
    {
        return function (
            string $attribute,
            mixed $value,
            \Closure $fail
        ): void {
            if ($this->isOptionalContactPlaceholder($value)) {
                return;
            }

            if (
                ! is_string($value)
                || filter_var($value, FILTER_VALIDATE_EMAIL) === false
            ) {
                $fail(
                    'Enter a valid email address. Leave blank or enter N/A or None if there is no email address.'
                );
            }
        };
    }

    private function validateResidencyStartYear(
        array $validated
    ): ?string {
        if (
            empty($validated['residency_start_year'])
            || empty($validated['birth_date'])
        ) {
            return null;
        }

        $birthYear = (int) date(
            'Y',
            strtotime($validated['birth_date'])
        );

        $residencyStartYear = (int) $validated['residency_start_year'];

        if ($residencyStartYear < $birthYear) {
            return 'Residency start year cannot be earlier than the resident\'s birth year.';
        }

        return null;
    }

    private function authorizeResidentManagement(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'You must be logged in to perform this action.');
        }

        if ($user->status !== 'active') {
            abort(403, 'Your account is inactive.');
        }

        if (in_array($user->role, ['captain', 'secretary'], true)) {
            return;
        }

        abort(403, 'You do not have permission to modify resident records.');
    }
}
