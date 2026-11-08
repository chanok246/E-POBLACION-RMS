<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HouseholdController extends Controller
{
    /**
     * Per-request cache of the real column list of each table that this
     * controller writes classification flags to.
     *
     * @var array<string, array<int, string>>
     */
    private array $tableColumnCache = [];

    /**
     * Philippine mobile number: +63 followed by exactly 10 digits, the first
     * of which must be 9 (e.g. +639171234567).
     */
    private const PH_MOBILE_PATTERN = '/^\+639\d{9}$/';

    /*
    |--------------------------------------------------------------------------
    | Household List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $selectedPurok = trim((string) $request->input('purok', ''));
        $selectedStatus = trim((string) $request->input('status', ''));

        $query = $this->filteredHouseholdQuery($search, $selectedPurok, $selectedStatus);

        $totalHouseholds = (clone $query)->count();

        $households = $query
            ->orderBy('purok')
            ->orderBy('household_number')
            ->paginate(10)
            ->withQueryString();

        $totalMembers = $households
            ->getCollection()
            ->sum(fn ($household) => $household->members->count());

        $puroks = $this->purokOptions();

        return view(
            'households.index',
            compact(
                'households',
                'puroks',
                'selectedPurok',
                'selectedStatus',
                'search',
                'totalHouseholds',
                'totalMembers'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Household Masterlist
    |--------------------------------------------------------------------------
    */

    public function masterlist(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $selectedPurok = trim((string) $request->input('purok', ''));
        $selectedStatus = trim((string) $request->input('status', ''));

        $households = $this->filteredHouseholdQuery($search, $selectedPurok, $selectedStatus)
            ->orderBy('purok')
            ->orderBy('household_number')
            ->get();

        $puroks = $this->purokOptions();

        $totalHouseholds = $households->count();

        $totalMembers = $households->sum(
            fn ($household) => $household->members->count()
        );

        $masterlistTitle = 'Household Masterlist';

        if ($selectedPurok !== '') {
            $masterlistTitle .= ' - ' . $selectedPurok;
        }

        return view(
            'households.masterlist',
            compact(
                'households',
                'puroks',
                'selectedPurok',
                'selectedStatus',
                'search',
                'totalHouseholds',
                'totalMembers',
                'masterlistTitle'
            )
        );
    }

    /**
     * Shared search / purok / status filtering used by index() and masterlist().
     */
    private function filteredHouseholdQuery(
        string $search,
        string $selectedPurok,
        string $selectedStatus
    ) {
        $query = Household::query()
            ->with([
                'householdHead',
                'members.resident',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('household_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('purok', 'like', "%{$search}%")
                    ->orWhereHas('householdHead', function ($head) use ($search) {
                        $head->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($selectedPurok !== '') {
            $query->where('purok', $selectedPurok);
        }

        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        return $query;
    }

    private function purokOptions()
    {
        return collect(range(1, 7))
            ->map(fn ($number) => 'Purok ' . $number);
    }

    /*
    |--------------------------------------------------------------------------
    | Household Map
    |--------------------------------------------------------------------------
    */

    public function map(): View
    {
        $households = Household::query()
            ->with([
                'householdHead',
                'members.resident',
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('purok')
            ->orderBy('household_number')
            ->get();

        return view(
            'household-map.index',
            compact('households')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Household Map Data
    |--------------------------------------------------------------------------
    */

    public function mapData(): JsonResponse
    {
        $households = Household::query()
            ->with([
                'householdHead',
                'members.resident',
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('purok')
            ->orderBy('household_number')
            ->get();

        $data = $households->map(
            function ($household) {
                $head = $household->householdHead;

                return [
                    'id' => $household->id,
                    'household_number' => $household->household_number,
                    'purok' => $household->purok,
                    'address' => $household->address,
                    'latitude' => (float) $household->latitude,
                    'longitude' => (float) $household->longitude,
                    'status' => $household->status,
                    'member_count' => $household->members->count(),
                    'household_head' => $head
                        ? $this->residentFullName($head)
                        : 'No household head',
                ];
            }
        );

        return response()->json($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Household
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $this->authorizeHouseholdManagement();

        $residents = $this->activeResidentsForForm();

        return view(
            'households.create',
            compact('residents')
        );
    }

    private function activeResidentsForForm()
    {
        return Resident::query()
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'birth_date',
                'sex',
                'civil_status',
                'contact_number',
                'email',
                'address',
                'occupation',
                'residency_type',
                'residency_start_year',
                'residency_year_estimated',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Household
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        // Normalize every contact number (+639XXXXXXXXX) BEFORE validation.
        $this->normalizeContactNumbers($request);

        $civilStatuses = $this->civilStatusOptions();
        $occupations = $this->occupationOptions();
        $educationLevels = $this->studentEducationLevels();

        $validated = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'purok' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Household classifications
            'is_low_income' => ['nullable', 'boolean'],
            'is_4ps_beneficiary' => ['nullable', 'boolean'],

            // Household head
            'head' => ['required', 'array'],
            'head.resident_id' => ['nullable', 'integer', 'exists:residents,id'],
            'head.first_name' => ['required', 'string', 'max:100'],
            'head.middle_name' => ['nullable', 'string', 'max:100'],
            'head.last_name' => ['required', 'string', 'max:100'],
            'head.suffix' => ['nullable', 'string', 'max:30'],
            'head.birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'head.sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'head.civil_status' => ['nullable', Rule::in($civilStatuses)],
            'head.contact_number' => ['nullable', 'string', 'max:13', 'regex:' . self::PH_MOBILE_PATTERN],
            'head.email' => ['nullable', 'string', 'max:255'],
            'head.occupation' => ['nullable', Rule::in($occupations)],
            'head.occupation_other' => ['nullable', 'string', 'max:255'],
            'head.residency_type' => [
                'required',
                Rule::in(['Original Resident', 'Migrant Resident']),
            ],
            'head.residency_start_year' => [
                'nullable',
                'integer',
                'digits:4',
                'min:1900',
                'max:' . now()->year,
            ],
            'head.residency_year_estimated' => ['nullable', 'boolean'],

            // Household head individual classifications
            'head.is_pwd' => ['nullable', 'boolean'],
            'head.is_senior_citizen' => ['nullable', 'boolean'],
            'head.is_student' => ['nullable', 'boolean'],
            'head.is_solo_parent' => ['nullable', 'boolean'],
            'head.student_education_level' => ['nullable', Rule::in($educationLevels)],
            'head.is_high_school' => ['nullable', 'boolean'], // legacy

            // Household members
            'members' => ['nullable', 'array'],
            'members.*.resident_id' => ['nullable', 'integer', 'exists:residents,id'],
            'members.*.first_name' => ['required', 'string', 'max:100'],
            'members.*.middle_name' => ['nullable', 'string', 'max:100'],
            'members.*.last_name' => ['required', 'string', 'max:100'],
            'members.*.suffix' => ['nullable', 'string', 'max:30'],
            'members.*.birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'members.*.sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'members.*.civil_status' => ['nullable', Rule::in($civilStatuses)],
            'members.*.relationship' => ['required', 'string', 'max:100'],
            'members.*.residency_type' => [
                'required',
                Rule::in(['Original Resident', 'Migrant Resident']),
            ],
            'members.*.residency_start_year' => [
                'nullable',
                'integer',
                'digits:4',
                'min:1900',
                'max:' . now()->year,
            ],
            'members.*.residency_year_estimated' => ['nullable', 'boolean'],
            'members.*.contact_number' => ['nullable', 'string', 'max:13', 'regex:' . self::PH_MOBILE_PATTERN],
            'members.*.email' => ['nullable', 'string', 'max:255'],
            'members.*.occupation' => ['nullable', Rule::in($occupations)],
            'members.*.occupation_other' => ['nullable', 'string', 'max:255'],

            // Household member individual classifications
            'members.*.is_pwd' => ['nullable', 'boolean'],
            'members.*.is_senior_citizen' => ['nullable', 'boolean'],
            'members.*.is_student' => ['nullable', 'boolean'],
            'members.*.is_solo_parent' => ['nullable', 'boolean'],
            'members.*.student_education_level' => ['nullable', Rule::in($educationLevels)],
            'members.*.is_high_school' => ['nullable', 'boolean'], // legacy
        ], $this->contactNumberMessages());

        /*
        |--------------------------------------------------------------------------
        | Schema Check
        |--------------------------------------------------------------------------
        |
        | Classifications must never be silently dropped. If the database
        | does not have the required columns yet, nothing is saved and the
        | missing columns are reported.
        |
        */

        $missingColumns = $this->missingClassificationColumns(true, true);

        if (!empty($missingColumns)) {
            return back()
                ->withErrors(['schema' => $this->missingColumnsMessage($missingColumns)])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Email Validation
        |--------------------------------------------------------------------------
        | Blank and "N/A" are allowed; otherwise must be a valid email.
        */

        $emailErrors = [];

        $headEmail = trim((string) ($validated['head']['email'] ?? ''));

        if (
            $headEmail !== ''
            && strtoupper($headEmail) !== 'N/A'
            && !filter_var($headEmail, FILTER_VALIDATE_EMAIL)
        ) {
            $emailErrors['head.email'] =
                'Please enter a valid email address, N/A, or leave the field blank.';
        }

        foreach (($validated['members'] ?? []) as $index => $member) {
            $email = trim((string) ($member['email'] ?? ''));

            if (
                $email !== ''
                && strtoupper($email) !== 'N/A'
                && !filter_var($email, FILTER_VALIDATE_EMAIL)
            ) {
                $emailErrors["members.{$index}.email"] =
                    'Please enter a valid email address, N/A, or leave the field blank.';
            }
        }

        if (!empty($emailErrors)) {
            return back()->withErrors($emailErrors)->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Occupation "Other" Validation
        |--------------------------------------------------------------------------
        */

        $occupationErrors = [];

        if (
            ($validated['head']['occupation'] ?? null) === 'Other'
            && trim((string) ($validated['head']['occupation_other'] ?? '')) === ''
        ) {
            $occupationErrors['head.occupation_other'] =
                'Please specify the occupation when "Other" is selected.';
        }

        foreach (($validated['members'] ?? []) as $index => $member) {
            if (
                ($member['occupation'] ?? null) === 'Other'
                && trim((string) ($member['occupation_other'] ?? '')) === ''
            ) {
                $occupationErrors["members.{$index}.occupation_other"] =
                    'Please specify the occupation when "Other" is selected.';
            }
        }

        if (!empty($occupationErrors)) {
            return back()->withErrors($occupationErrors)->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Student Education Level Validation
        |--------------------------------------------------------------------------
        | Every person marked as Student needs an education level. Each person
        | is evaluated on their own; nothing is shared between people.
        */

        $studentErrors = [];

        $headStudent = $this->resolveStudentClassification($validated['head']);

        if ($headStudent['is_student'] && $headStudent['level'] === null) {
            $studentErrors['head.student_education_level'] =
                'Please select the education level (Elementary, High School, or College) for the household head.';
        }

        foreach (($validated['members'] ?? []) as $index => $member) {
            $student = $this->resolveStudentClassification($member);

            if ($student['is_student'] && $student['level'] === null) {
                $studentErrors["members.{$index}.student_education_level"] =
                    'Please select the education level (Elementary, High School, or College) for each student.';
            }
        }

        if (!empty($studentErrors)) {
            return back()->withErrors($studentErrors)->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Coordinates Must Be Supplied Together
        |--------------------------------------------------------------------------
        */

        $coordinateError = $this->coordinatePairError($validated);

        if ($coordinateError !== null) {
            return back()->withErrors($coordinateError)->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Residency Year Validation
        |--------------------------------------------------------------------------
        */

        $currentYear = now()->year;

        if (($validated['head']['residency_type'] ?? null) === 'Migrant Resident') {
            if (empty($validated['head']['residency_start_year'] ?? null)) {
                return back()
                    ->withErrors([
                        'head.residency_start_year' =>
                            'Residency start year is required for a Migrant Resident.',
                    ])
                    ->withInput();
            }

            if ((int) $validated['head']['residency_start_year'] > $currentYear) {
                return back()
                    ->withErrors([
                        'head.residency_start_year' =>
                            'Residency start year cannot be in the future.',
                    ])
                    ->withInput();
            }
        }

        foreach (($validated['members'] ?? []) as $index => $member) {
            if (($member['residency_type'] ?? null) !== 'Migrant Resident') {
                continue;
            }

            if (empty($member['residency_start_year'] ?? null)) {
                return back()
                    ->withErrors([
                        "members.{$index}.residency_start_year" =>
                            'Residency start year is required for a Migrant Resident.',
                    ])
                    ->withInput();
            }

            if ((int) $member['residency_start_year'] > $currentYear) {
                return back()
                    ->withErrors([
                        "members.{$index}.residency_start_year" =>
                            'Residency start year cannot be in the future.',
                    ])
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate People Inside One Submission
        |--------------------------------------------------------------------------
        */

        $submittedPeople = [];

        $submittedPeople[$this->personIdentityKey($validated['head'])] = 'head';

        foreach (($validated['members'] ?? []) as $index => $member) {
            $memberIdentity = $this->personIdentityKey($member);

            if (isset($submittedPeople[$memberIdentity])) {
                return back()
                    ->withErrors([
                        "members.{$index}.first_name" =>
                            'This person has already been registered in this household.',
                    ])
                    ->withInput();
            }

            $submittedPeople[$memberIdentity] = "member_{$index}";
        }

        /*
        |--------------------------------------------------------------------------
        | Create Household + Residents + Memberships
        |--------------------------------------------------------------------------
        */

        $householdNumber = DB::transaction(
            function () use ($validated) {

                do {
                    $number =
                        'HH-' .
                        strtoupper(
                            substr(
                                str_replace('-', '', uniqid('', true)),
                                -6
                            )
                        );
                } while (
                    Household::query()
                        ->where('household_number', $number)
                        ->exists()
                );

                $head = $this->findOrCreateResident(
                    $validated['head'],
                    $validated['address']
                );

                /*
                | Household is written with ONE save(). Classification flags are
                | applied with forceFill, restricted to columns that really exist.
                */

                $household = new Household([
                    'household_number' => $number,
                    'address' => $validated['address'],
                    'purok' => $validated['purok'],
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'household_head_id' => $head->id,
                    'status' => 'Active',
                ]);

                $household->forceFill(
                    $this->householdClassificationAttributes($validated)
                );

                $household->save();

                // The head is also a household member; their own
                // individual classifications are stored on this record.
                $this->createMembership(
                    $household,
                    $head,
                    'Head',
                    $validated['head']
                );

                foreach (($validated['members'] ?? []) as $memberData) {
                    $resident = $this->findOrCreateResident(
                        $memberData,
                        $validated['address']
                    );

                    $alreadyMember = HouseholdMember::query()
                        ->where('household_id', $household->id)
                        ->where('resident_id', $resident->id)
                        ->exists();

                    if ($alreadyMember) {
                        continue;
                    }

                    // Each member is saved with THEIR OWN classifications only.
                    $this->createMembership(
                        $household,
                        $resident,
                        $memberData['relationship'],
                        $memberData
                    );
                }

                return $number;
            }
        );

        return redirect()
            ->route('households.index')
            ->with(
                'success',
                "Household {$householdNumber} was successfully registered through house-to-house registration."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Find Existing Resident or Create New Resident
    |--------------------------------------------------------------------------
    | The household address is the source of truth.
    */

    private function findOrCreateResident(
        array $person,
        string $householdAddress
    ): Resident {

        // Existing resident selected
        if (!empty($person['resident_id'])) {
            $existingResident = Resident::query()
                ->whereKey($person['resident_id'])
                ->first();

            if (!$existingResident) {
                abort(422, 'The selected resident could not be found.');
            }

            if ($existingResident->status !== 'Active') {
                abort(422, 'The selected resident is not active.');
            }

            $existingResident->update([
                'address' => $householdAddress,
                'status' => 'Active',
                'residency_type' =>
                    $person['residency_type']
                    ?? $existingResident->residency_type
                    ?? 'Original Resident',
                'residency_start_year' =>
                    !empty($person['residency_start_year'])
                        ? (int) $person['residency_start_year']
                        : null,
                'residency_year_estimated' =>
                    $this->booleanValue(
                        $person['residency_year_estimated'] ?? false
                    ),
            ]);

            return $existingResident->fresh();
        }

        $firstName = trim((string) ($person['first_name'] ?? ''));
        $middleName = trim((string) ($person['middle_name'] ?? ''));
        $lastName = trim((string) ($person['last_name'] ?? ''));
        $suffix = trim((string) ($person['suffix'] ?? ''));

        // Identity check before creating a new individual record
        $query = Resident::query()
            ->where('first_name', $firstName)
            ->where('last_name', $lastName);

        if ($middleName !== '') {
            $query->where('middle_name', $middleName);
        } else {
            $query->where(function ($q) {
                $q->whereNull('middle_name')->orWhere('middle_name', '');
            });
        }

        if ($suffix !== '') {
            $query->where('suffix', $suffix);
        } else {
            $query->where(function ($q) {
                $q->whereNull('suffix')->orWhere('suffix', '');
            });
        }

        if (!empty($person['birth_date'])) {
            $query->whereDate(
                'birth_date',
                Carbon::parse($person['birth_date'])->toDateString()
            );
        } else {
            $query->whereNull('birth_date');
        }

        $resident = $query->first();

        $email = trim((string) ($person['email'] ?? ''));

        if ($email === '') {
            $email = null;
        } elseif (strtoupper($email) === 'N/A') {
            $email = 'N/A';
        }

        $occupation = $this->resolveOccupation($person);

        // Stored as +639XXXXXXXXX, or null when the optional field is blank.
        $contactNumber = $this->contactNumberForStorage($person['contact_number'] ?? null);

        $attributes = [
            'first_name' => $firstName,
            'middle_name' => $middleName !== '' ? $middleName : null,
            'last_name' => $lastName,
            'suffix' => $suffix !== '' ? $suffix : null,
            'birth_date' => !empty($person['birth_date']) ? $person['birth_date'] : null,
            'sex' => $person['sex'] ?? null,
            'civil_status' => $person['civil_status'] ?? null,
            'contact_number' => $contactNumber,
            'email' => $email,
            'address' => $householdAddress, // household address always wins
            'occupation' => $occupation,
            'residency_type' => $person['residency_type'] ?? 'Original Resident',
            'residency_start_year' =>
                !empty($person['residency_start_year'])
                    ? (int) $person['residency_start_year']
                    : null,
            'residency_year_estimated' =>
                $this->booleanValue($person['residency_year_estimated'] ?? false),
            'status' => 'Active',
        ];

        if ($resident) {
            $resident->update($attributes);

            return $resident->fresh();
        }

        return Resident::create($attributes);
    }

    /*
    |--------------------------------------------------------------------------
    | Philippine Contact Numbers
    |--------------------------------------------------------------------------
    | Contact numbers stay OPTIONAL. Accepted input representations:
    |     +639XXXXXXXXX   (the form's format)
    |     639XXXXXXXXX
    |     09XXXXXXXXX     (local 11-digit format)
    | All are normalized to +639XXXXXXXXX before validation and saving.
    | Anything else (letters, wrong length, prefix other than 9, malformed
    | country code) is left untouched so the validator rejects it.
    */

    /**
     * Blank becomes null; a recognised Philippine mobile number becomes
     * +639XXXXXXXXX; any other value is returned trimmed so validation
     * can reject it.
     */
    private function normalizePhilippineMobile(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match(self::PH_MOBILE_PATTERN, $value) === 1) {
            return $value;
        }

        if (preg_match('/^639\d{9}$/', $value) === 1) {
            return '+' . $value;
        }

        if (preg_match('/^09\d{9}$/', $value) === 1) {
            return '+63' . substr($value, 1);
        }

        return $value;
    }

    /**
     * Storable value: the normalized +639XXXXXXXXX, or null.
     */
    private function contactNumberForStorage(mixed $value): ?string
    {
        $normalized = $this->normalizePhilippineMobile($value);

        return is_string($normalized)
            && preg_match(self::PH_MOBILE_PATTERN, $normalized) === 1
                ? $normalized
                : null;
    }

    /**
     * Normalize head.contact_number and every members.*.contact_number
     * in the request before validation.
     */
    private function normalizeContactNumbers(Request $request): void
    {
        $head = $request->input('head');

        if (is_array($head) && array_key_exists('contact_number', $head)) {
            $head['contact_number'] =
                $this->normalizePhilippineMobile($head['contact_number']);

            $request->merge(['head' => $head]);
        }

        $members = $request->input('members');

        if (is_array($members)) {
            foreach ($members as $index => $member) {
                if (is_array($member) && array_key_exists('contact_number', $member)) {
                    $members[$index]['contact_number'] =
                        $this->normalizePhilippineMobile($member['contact_number']);
                }
            }

            $request->merge(['members' => $members]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function contactNumberMessages(): array
    {
        $head = 'The household head contact number must be a valid Philippine mobile number: '
            . '+63 followed by 10 digits starting with 9 (e.g. +639171234567).';

        $member = 'Each household member contact number must be a valid Philippine mobile number: '
            . '+63 followed by 10 digits starting with 9 (e.g. +639171234567).';

        return [
            'head.contact_number.regex' => $head,
            'head.contact_number.max' => $head,
            'head.contact_number.string' => $head,
            'members.*.contact_number.regex' => $member,
            'members.*.contact_number.max' => $member,
            'members.*.contact_number.string' => $member,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Schema Guards
    |--------------------------------------------------------------------------
    */

    private function tableHasColumn(string $table, string $column): bool
    {
        if (!isset($this->tableColumnCache[$table])) {
            $this->tableColumnCache[$table] =
                Schema::hasTable($table)
                    ? Schema::getColumnListing($table)
                    : [];
        }

        return in_array($column, $this->tableColumnCache[$table], true);
    }

    /**
     * Keep only the attributes whose column exists in the given table.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function onlyExistingColumns(string $table, array $attributes): array
    {
        return array_filter(
            $attributes,
            fn ($value, $column) => $this->tableHasColumn($table, (string) $column),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Columns required to save classifications without losing data.
     * (is_high_school / is_high_school_student are legacy and optional.)
     *
     * @return array<int, string>  e.g. ["households.is_low_income"]
     */
    private function missingClassificationColumns(
        bool $household,
        bool $members
    ): array {
        $required = [];

        if ($household) {
            $required['households'] = ['is_low_income', 'is_4ps_beneficiary'];
        }

        if ($members) {
            $required['household_members'] = [
                'is_pwd',
                'is_senior_citizen',
                'is_solo_parent',
                'is_student',
                'education_level',
            ];
        }

        $missing = [];

        foreach ($required as $table => $columns) {
            foreach ($columns as $column) {
                if (!$this->tableHasColumn($table, $column)) {
                    $missing[] = "{$table}.{$column}";
                }
            }
        }

        return $missing;
    }

    private function missingColumnsMessage(array $missing): string
    {
        return 'Nothing was saved. The database is missing required classification columns: '
            . implode(', ', $missing)
            . '. Please add them with a safe, non-destructive migration before registering households.';
    }

    /*
    |--------------------------------------------------------------------------
    | Household Classification Attributes
    |--------------------------------------------------------------------------
    |     Low-Income / Indigent  => is_low_income
    |     4Ps Beneficiary        => is_4ps_beneficiary
    | Both can be true at the same time.
    */

    private function householdClassificationAttributes(array $data): array
    {
        return $this->onlyExistingColumns(
            'households',
            [
                'is_low_income' =>
                    $this->booleanValue($data['is_low_income'] ?? false),

                'is_4ps_beneficiary' =>
                    $this->booleanValue($data['is_4ps_beneficiary'] ?? false),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Student Classification
    |--------------------------------------------------------------------------
    |
    | education_level values: Elementary | High School | College
    |
    | Legacy compatibility:
    |   - A submission that only carries is_high_school = true is treated as
    |     Student + High School.
    |   - is_high_school and is_high_school_student are DERIVED from the
    |     education level (true only for Student + High School) so existing
    |     code/data that reads them keeps working.
    |
    */

    /**
     * @return array{is_student: bool, level: ?string, is_high_school: bool}
     */
    private function resolveStudentClassification(array $person): array
    {
        $legacyHighSchool = $this->booleanValue($person['is_high_school'] ?? false);

        $isStudent = $this->booleanValue($person['is_student'] ?? false)
            || $legacyHighSchool;

        if (!$isStudent) {
            return [
                'is_student' => false,
                'level' => null,
                'is_high_school' => false,
            ];
        }

        $level = trim((string) ($person['student_education_level'] ?? ''));

        if (!in_array($level, $this->studentEducationLevels(), true)) {
            $level = '';
        }

        if ($level === '' && $legacyHighSchool) {
            $level = 'High School';
        }

        return [
            'is_student' => true,
            'level' => $level !== '' ? $level : null,
            'is_high_school' => $level === 'High School',
        ];
    }

    private function studentEducationLevels(): array
    {
        return ['Elementary', 'High School', 'College'];
    }

    /*
    |--------------------------------------------------------------------------
    | Member Classification Attributes (per person)
    |--------------------------------------------------------------------------
    |     PWD             => is_pwd
    |     Senior Citizen  => is_senior_citizen
    |     Solo Parent     => is_solo_parent
    |     Student         => is_student + education_level
    */

    private function memberClassificationAttributes(array $person): array
    {
        $student = $this->resolveStudentClassification($person);

        return $this->onlyExistingColumns(
            'household_members',
            [
                'is_pwd' =>
                    $this->booleanValue($person['is_pwd'] ?? false),

                'is_senior_citizen' =>
                    $this->booleanValue($person['is_senior_citizen'] ?? false),

                'is_solo_parent' =>
                    $this->booleanValue($person['is_solo_parent'] ?? false),

                'is_student' =>
                    $student['is_student'],

                'education_level' =>
                    $student['level'],

                // Legacy columns kept in sync (only written if they exist).
                'is_high_school' =>
                    $student['is_high_school'],

                'is_high_school_student' =>
                    $student['is_high_school'],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Household Membership (single save)
    |--------------------------------------------------------------------------
    */

    private function createMembership(
        Household $household,
        Resident $resident,
        string $relationship,
        array $person = []
    ): HouseholdMember {
        return HouseholdMember::forceCreate(
            $this->onlyExistingColumns(
                'household_members',
                array_merge(
                    [
                        'household_id' => $household->id,
                        'resident_id' => $resident->id,
                        'relationship' => $relationship,
                    ],
                    $this->memberClassificationAttributes($person)
                )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    */

    private function civilStatusOptions(): array
    {
        return [
            'Single',
            'Married',
            'Widowed',
            'Divorced',
            'Separated',
            'Live-in / Cohabiting',
            'Annulled',
            'Not Applicable',
            'Prefer not to say',
        ];
    }

    private function occupationOptions(): array
    {
        return [
            'Student',
            'Employed',
            'Self-Employed',
            'Business Owner',
            'Farmer',
            'Fisherman',
            'Homemaker',
            'Unemployed',
            'Retired',
            'Senior Citizen',
            'Child / Not Yet School-Age',
            'Infant',
            'Other',
        ];
    }

    /**
     * If "Other" is selected, the specified occupation becomes the stored value.
     */
    private function resolveOccupation(array $person): ?string
    {
        $occupation = trim((string) ($person['occupation'] ?? ''));

        if ($occupation === '') {
            return null;
        }

        if ($occupation !== 'Other') {
            return $occupation;
        }

        $other = trim((string) ($person['occupation_other'] ?? ''));

        return $other !== '' ? $other : 'Other';
    }

    private function booleanValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns an error array when only one of latitude/longitude is given.
     *
     * @return array<string, string>|null
     */
    private function coordinatePairError(array $validated): ?array
    {
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;

        if ($latitude !== null && $longitude === null) {
            return ['longitude' => 'Longitude is required when latitude is provided.'];
        }

        if ($longitude !== null && $latitude === null) {
            return ['latitude' => 'Latitude is required when longitude is provided.'];
        }

        return null;
    }

    private function personIdentityKey(array $person): string
    {
        return strtolower(
            trim((string) ($person['first_name'] ?? ''))
            . '|'
            . trim((string) ($person['middle_name'] ?? ''))
            . '|'
            . trim((string) ($person['last_name'] ?? ''))
            . '|'
            . trim((string) ($person['suffix'] ?? ''))
            . '|'
            . trim((string) ($person['birth_date'] ?? ''))
        );
    }

    private function residentFullName(Resident $resident): string
    {
        return trim(
            $resident->first_name
            . ' '
            . ($resident->middle_name ? $resident->middle_name . ' ' : '')
            . $resident->last_name
            . ($resident->suffix ? ' ' . $resident->suffix : '')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Household
    |--------------------------------------------------------------------------
    */

    public function show(Household $household): View
    {
        $household->load([
            'householdHead',
            'members.resident',
        ]);

        return view(
            'households.show',
            compact('household')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Household
    |--------------------------------------------------------------------------
    */

    public function edit(Household $household): View
    {
        $this->authorizeHouseholdManagement();

        $household->load([
            'householdHead',
            'members.resident',
        ]);

        $residents = $this->activeResidentsForForm();

        return view(
            'households.edit',
            compact('household', 'residents')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Household
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Household $household): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        $validated = $request->validate([
            'address' => ['required', 'string', 'max:255'],
            'purok' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'household_head_id' => ['required', 'integer', 'exists:residents,id'],

            // Optional on this form: only applied when actually submitted.
            'is_low_income' => ['nullable', 'boolean'],
            'is_4ps_beneficiary' => ['nullable', 'boolean'],
        ]);

        $coordinateError = $this->coordinatePairError($validated);

        if ($coordinateError !== null) {
            return back()->withErrors($coordinateError)->withInput();
        }

        $householdHead = Resident::query()
            ->whereKey($validated['household_head_id'])
            ->where('status', 'Active')
            ->first();

        if (!$householdHead) {
            return back()
                ->withErrors([
                    'household_head_id' =>
                        'The selected household head is not an active resident.',
                ])
                ->withInput();
        }

        // Household classifications actually submitted
        $submittedClassifications = [];

        foreach (['is_low_income', 'is_4ps_beneficiary'] as $field) {
            if (array_key_exists($field, $validated)) {
                $submittedClassifications[$field] = $validated[$field];
            }
        }

        if (!empty($submittedClassifications)) {
            $missingColumns = array_filter(
                $this->missingClassificationColumns(true, false),
                fn ($column) => in_array(
                    str_replace('households.', '', $column),
                    array_keys($submittedClassifications),
                    true
                )
            );

            if (!empty($missingColumns)) {
                return back()
                    ->withErrors(['schema' => $this->missingColumnsMessage($missingColumns)])
                    ->withInput();
            }
        }

        $householdClassifications = $this->onlyExistingColumns(
            'households',
            array_map(
                fn ($value) => $this->booleanValue($value),
                $submittedClassifications
            )
        );

        DB::transaction(
            function () use ($validated, $household, $householdClassifications) {

                $oldHeadId = $household->household_head_id;
                $newHeadId = $validated['household_head_id'];

                // Update household (single save)
                $household->fill([
                    'address' => $validated['address'],
                    'purok' => $validated['purok'],
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'household_head_id' => $newHeadId,
                ]);

                if (!empty($householdClassifications)) {
                    $household->forceFill($householdClassifications);
                }

                $household->save();

                // All existing household members inherit the household address
                $memberResidentIds = HouseholdMember::query()
                    ->where('household_id', $household->id)
                    ->pluck('resident_id');

                if ($memberResidentIds->isNotEmpty()) {
                    Resident::query()
                        ->whereIn('id', $memberResidentIds)
                        ->update(['address' => $validated['address']]);
                }

                // New head also gets the household address
                Resident::query()
                    ->whereKey($newHeadId)
                    ->update(['address' => $validated['address']]);

                $newHeadMembership = HouseholdMember::query()
                    ->where('household_id', $household->id)
                    ->where('resident_id', $newHeadId)
                    ->first();

                // Remove head status from previous head
                if ($oldHeadId && (int) $oldHeadId !== (int) $newHeadId) {
                    HouseholdMember::query()
                        ->where('household_id', $household->id)
                        ->where('resident_id', $oldHeadId)
                        ->where('relationship', 'Head')
                        ->update(['relationship' => 'Former Head']);
                }

                if ($newHeadMembership) {
                    // Existing membership keeps its saved classifications
                    $newHeadMembership->update(['relationship' => 'Head']);
                } else {
                    $this->createMembership(
                        $household,
                        Resident::query()->findOrFail($newHeadId),
                        'Head',
                        []
                    );
                }

                // Safety: no other Head relationship may remain
                HouseholdMember::query()
                    ->where('household_id', $household->id)
                    ->where('relationship', 'Head')
                    ->where('resident_id', '!=', $newHeadId)
                    ->update(['relationship' => 'Former Head']);
            }
        );

        return redirect()
            ->route('households.show', $household)
            ->with(
                'success',
                "Household {$household->household_number} was successfully updated."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Archive / Restore / Delete
    |--------------------------------------------------------------------------
    */

    public function archive(Household $household): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        $household->update(['status' => 'Archived']);

        return redirect()
            ->route('households.index')
            ->with('success', "Household {$household->household_number} was archived.");
    }

    public function restore(Household $household): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        $household->update(['status' => 'Active']);

        return redirect()
            ->route('households.index')
            ->with('success', "Household {$household->household_number} was restored.");
    }

    public function destroy(Household $household): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        $householdNumber = $household->household_number;

        $household->delete();

        return redirect()
            ->route('households.index')
            ->with('success', "Household {$householdNumber} was permanently deleted.");
    }

    /*
    |--------------------------------------------------------------------------
    | Add Existing Household Member
    |--------------------------------------------------------------------------
    */

    public function addMember(Request $request, Household $household): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        $validated = $request->validate([
            'resident_id' => ['required', 'integer', 'exists:residents,id'],
            'relationship' => ['required', 'string', 'max:100'],

            'is_pwd' => ['nullable', 'boolean'],
            'is_senior_citizen' => ['nullable', 'boolean'],
            'is_student' => ['nullable', 'boolean'],
            'is_solo_parent' => ['nullable', 'boolean'],
            'student_education_level' => [
                'nullable',
                Rule::in($this->studentEducationLevels()),
            ],
            'is_high_school' => ['nullable', 'boolean'], // legacy
        ]);

        $missingColumns = $this->missingClassificationColumns(false, true);

        if (!empty($missingColumns)) {
            return back()
                ->withErrors(['schema' => $this->missingColumnsMessage($missingColumns)])
                ->withInput();
        }

        $student = $this->resolveStudentClassification($validated);

        if ($student['is_student'] && $student['level'] === null) {
            return back()
                ->withErrors([
                    'student_education_level' =>
                        'Please select the education level (Elementary, High School, or College) for a student.',
                ])
                ->withInput();
        }

        $resident = Resident::query()
            ->whereKey($validated['resident_id'])
            ->where('status', 'Active')
            ->first();

        if (!$resident) {
            return back()
                ->withErrors(['resident_id' => 'The selected resident is not active.'])
                ->withInput();
        }

        if (
            $household->household_head_id
            && (int) $household->household_head_id === (int) $resident->id
        ) {
            return back()
                ->withErrors(['resident_id' => 'This resident is already the household head.'])
                ->withInput();
        }

        $alreadyMember = HouseholdMember::query()
            ->where('household_id', $household->id)
            ->where('resident_id', $resident->id)
            ->exists();

        if ($alreadyMember) {
            return back()
                ->withErrors(['resident_id' => 'This resident is already a member of this household.'])
                ->withInput();
        }

        // Household address is the source of truth
        $resident->update(['address' => $household->address]);

        $this->createMembership(
            $household,
            $resident,
            $validated['relationship'],
            $validated
        );

        return back()
            ->with(
                'success',
                "{$resident->first_name} {$resident->last_name} was added to the household."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Household Member
    |--------------------------------------------------------------------------
    */

    public function removeMember(Household $household, Resident $resident): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        $member = HouseholdMember::query()
            ->where('household_id', $household->id)
            ->where('resident_id', $resident->id)
            ->first();

        if (!$member) {
            return back()
                ->withErrors(['resident' => 'The resident is not a member of this household.']);
        }

        if (strtolower((string) $member->relationship) === 'head') {
            return back()
                ->withErrors([
                    'resident' =>
                        'The household head cannot be removed here. Assign another head first.',
                ]);
        }

        $member->delete();

        return back()
            ->with(
                'success',
                "{$resident->first_name} {$resident->last_name} was removed from the household."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Household Management Authorization
    |--------------------------------------------------------------------------
    | Captain: full management. Secretary: full management. Treasurer: view only.
    */

    private function authorizeHouseholdManagement(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'You must be logged in to perform this action.');
        }

        if ($user->status !== 'active') {
            abort(403, 'Your account is inactive.');
        }

        if ($user->role === 'captain' || $user->role === 'secretary') {
            return;
        }

        abort(403, 'You do not have permission to modify household records.');
    }
}