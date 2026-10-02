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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HouseholdController extends Controller
{
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

        $query = Household::query()
            ->with([
                'householdHead',
                'members.resident',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'household_number',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'address',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'purok',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'householdHead',
                        function ($head) use ($search) {
                            $head->where(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )
                                ->orWhere(
                                    'middle_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Purok Filter
        |--------------------------------------------------------------------------
        */

        if ($selectedPurok !== '') {
            $query->where(
                'purok',
                $selectedPurok
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($selectedStatus !== '') {
            $query->where(
                'status',
                $selectedStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Total Matching Households
        |--------------------------------------------------------------------------
        */

        $totalHouseholds = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | Paginated Households
        |--------------------------------------------------------------------------
        */

        $households = $query
            ->orderBy('purok')
            ->orderBy('household_number')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Total Members
        |--------------------------------------------------------------------------
        */

        $totalMembers = $households
            ->getCollection()
            ->sum(
                fn ($household) => $household->members->count()
            );

        /*
        |--------------------------------------------------------------------------
        | Puroks
        |--------------------------------------------------------------------------
        */

        $puroks = collect(range(1, 7))
            ->map(
                fn ($number) => 'Purok ' . $number
            );

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

        $query = Household::query()
            ->with([
                'householdHead',
                'members.resident',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'household_number',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'address',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'purok',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'householdHead',
                        function ($head) use ($search) {
                            $head->where(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )
                                ->orWhere(
                                    'middle_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Purok Filter
        |--------------------------------------------------------------------------
        */

        if ($selectedPurok !== '') {
            $query->where(
                'purok',
                $selectedPurok
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($selectedStatus !== '') {
            $query->where(
                'status',
                $selectedStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Matching Households
        |--------------------------------------------------------------------------
        */

        $households = $query
            ->orderBy('purok')
            ->orderBy('household_number')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Puroks
        |--------------------------------------------------------------------------
        */

        $puroks = collect(range(1, 7))
            ->map(
                fn ($number) => 'Purok ' . $number
            );

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalHouseholds = $households->count();

        $totalMembers = $households->sum(
            fn ($household) => $household->members->count()
        );

        /*
        |--------------------------------------------------------------------------
        | Masterlist Title
        |--------------------------------------------------------------------------
        */

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

                    'household_number' =>
                        $household->household_number,

                    'purok' =>
                        $household->purok,

                    'address' =>
                        $household->address,

                    'latitude' =>
                        (float) $household->latitude,

                    'longitude' =>
                        (float) $household->longitude,

                    'status' =>
                        $household->status,

                    'member_count' =>
                        $household->members->count(),

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

        /*
        |--------------------------------------------------------------------------
        | Load Active Residents
        |--------------------------------------------------------------------------
        |
        | Existing resident records can be searched and selected instead
        | of creating duplicate individual records.
        |
        */

        $residents = Resident::query()
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

        return view(
            'households.create',
            compact('residents')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Household
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHouseholdManagement();

        /*
        |--------------------------------------------------------------------------
        | Allowed Civil Statuses
        |--------------------------------------------------------------------------
        */

        $civilStatuses = $this->civilStatusOptions();

        /*
        |--------------------------------------------------------------------------
        | Allowed Occupations
        |--------------------------------------------------------------------------
        */

        $occupations = $this->occupationOptions();

        /*
        |--------------------------------------------------------------------------
        | Validate Household Information
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'purok' => [
                'required',
                'string',
                'max:100',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            /*
            |--------------------------------------------------------------------------
            | Household Head
            |--------------------------------------------------------------------------
            */

            'head' => [
                'required',
                'array',
            ],

            'head.resident_id' => [
                'nullable',
                'integer',
                'exists:residents,id',
            ],

            'head.first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'head.middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'head.last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'head.suffix' => [
                'nullable',
                'string',
                'max:30',
            ],

            'head.birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'head.sex' => [
                'nullable',
                Rule::in(['Male', 'Female']),
            ],

            'head.civil_status' => [
                'nullable',
                Rule::in($civilStatuses),
            ],

            'head.contact_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'head.email' => [
                'nullable',
                'string',
                'max:255',
            ],

            'head.occupation' => [
                'nullable',
                Rule::in($occupations),
            ],

            'head.occupation_other' => [
                'nullable',
                'string',
                'max:255',
            ],

            'head.residency_type' => [
                'required',
                Rule::in([
                    'Original Resident',
                    'Migrant Resident',
                ]),
            ],

            'head.residency_start_year' => [
                'nullable',
                'integer',
                'digits:4',
                'min:1900',
                'max:' . now()->year,
            ],

            'head.residency_year_estimated' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Household Head Classifications
            |--------------------------------------------------------------------------
            */

            'head.is_pwd' => [
                'nullable',
                'boolean',
            ],

            'head.is_solo_parent' => [
                'nullable',
                'boolean',
            ],

            'head.is_high_school_student' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Household Members
            |--------------------------------------------------------------------------
            */

            'members' => [
                'nullable',
                'array',
            ],

            'members.*.resident_id' => [
                'nullable',
                'integer',
                'exists:residents,id',
            ],

            'members.*.first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'members.*.middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'members.*.last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'members.*.suffix' => [
                'nullable',
                'string',
                'max:30',
            ],

            'members.*.birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'members.*.sex' => [
                'nullable',
                Rule::in(['Male', 'Female']),
            ],

            'members.*.civil_status' => [
                'nullable',
                Rule::in($civilStatuses),
            ],

            'members.*.relationship' => [
                'required',
                'string',
                'max:100',
            ],

            'members.*.residency_type' => [
                'required',
                Rule::in([
                    'Original Resident',
                    'Migrant Resident',
                ]),
            ],

            'members.*.residency_start_year' => [
                'nullable',
                'integer',
                'digits:4',
                'min:1900',
                'max:' . now()->year,
            ],

            'members.*.residency_year_estimated' => [
                'nullable',
                'boolean',
            ],

            'members.*.contact_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'members.*.email' => [
                'nullable',
                'string',
                'max:255',
            ],

            'members.*.occupation' => [
                'nullable',
                Rule::in($occupations),
            ],

            'members.*.occupation_other' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Household Member Classifications
            |--------------------------------------------------------------------------
            */

            'members.*.is_pwd' => [
                'nullable',
                'boolean',
            ],

            'members.*.is_solo_parent' => [
                'nullable',
                'boolean',
            ],

            'members.*.is_high_school_student' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Email Values
        |--------------------------------------------------------------------------
        |
        | Blank values are allowed.
        | "N/A" is allowed.
        | Otherwise, the value must be a valid email address.
        |
        */

        $emailErrors = [];

        $headEmail = trim(
            (string) (
                $validated['head']['email'] ?? ''
            )
        );

        if (
            $headEmail !== ''
            && strtoupper($headEmail) !== 'N/A'
            && !filter_var($headEmail, FILTER_VALIDATE_EMAIL)
        ) {
            $emailErrors['head.email'] =
                'Please enter a valid email address, N/A, or leave the field blank.';
        }

        foreach (
            ($validated['members'] ?? []) as $index => $member
        ) {
            $email = trim(
                (string) (
                    $member['email'] ?? ''
                )
            );

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
            return back()
                ->withErrors($emailErrors)
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Occupation "Other"
        |--------------------------------------------------------------------------
        */

        $occupationErrors = [];

        $headOccupation =
            $validated['head']['occupation'] ?? null;

        $headOccupationOther = trim(
            (string) (
                $validated['head']['occupation_other'] ?? ''
            )
        );

        if (
            $headOccupation === 'Other'
            && $headOccupationOther === ''
        ) {
            $occupationErrors['head.occupation_other'] =
                'Please specify the occupation when "Other" is selected.';
        }

        foreach (
            ($validated['members'] ?? []) as $index => $member
        ) {
            $occupation =
                $member['occupation'] ?? null;

            $occupationOther = trim(
                (string) (
                    $member['occupation_other'] ?? ''
                )
            );

            if (
                $occupation === 'Other'
                && $occupationOther === ''
            ) {
                $occupationErrors["members.{$index}.occupation_other"] =
                    'Please specify the occupation when "Other" is selected.';
            }
        }

        if (!empty($occupationErrors)) {
            return back()
                ->withErrors($occupationErrors)
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Coordinates Must Be Supplied Together
        |--------------------------------------------------------------------------
        */

        if (
            ($validated['latitude'] ?? null) !== null
            && ($validated['longitude'] ?? null) === null
        ) {
            return back()
                ->withErrors([
                    'longitude' =>
                        'Longitude is required when latitude is provided.',
                ])
                ->withInput();
        }

        if (
            ($validated['longitude'] ?? null) !== null
            && ($validated['latitude'] ?? null) === null
        ) {
            return back()
                ->withErrors([
                    'latitude' =>
                        'Latitude is required when longitude is provided.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Residency Year Validation
        |--------------------------------------------------------------------------
        |
        | The system no longer uses an exact residency date.
        |
        | The year is enough for the household/resident record.
        |
        */

        $currentYear = now()->year;

        if (
            ($validated['head']['residency_type'] ?? null)
            === 'Migrant Resident'
        ) {
            if (
                empty(
                    $validated['head']['residency_start_year']
                    ?? null
                )
            ) {
                return back()
                    ->withErrors([
                        'head.residency_start_year' =>
                            'Residency start year is required for a Migrant Resident.',
                    ])
                    ->withInput();
            }

            if (
                (int) $validated['head']['residency_start_year']
                > $currentYear
            ) {
                return back()
                    ->withErrors([
                        'head.residency_start_year' =>
                            'Residency start year cannot be in the future.',
                    ])
                    ->withInput();
            }
        }

        foreach (
            ($validated['members'] ?? []) as $index => $member
        ) {
            if (
                ($member['residency_type'] ?? null)
                !== 'Migrant Resident'
            ) {
                continue;
            }

            if (
                empty(
                    $member['residency_start_year']
                    ?? null
                )
            ) {
                return back()
                    ->withErrors([
                        "members.{$index}.residency_start_year" =>
                            'Residency start year is required for a Migrant Resident.',
                    ])
                    ->withInput();
            }

            if (
                (int) $member['residency_start_year']
                > $currentYear
            ) {
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

        /*
        |--------------------------------------------------------------------------
        | Head Identity
        |--------------------------------------------------------------------------
        */

        $headIdentity = $this->personIdentityKey(
            $validated['head']
        );

        $submittedPeople[$headIdentity] = 'head';

        /*
        |--------------------------------------------------------------------------
        | Member Identities
        |--------------------------------------------------------------------------
        */

        foreach (
            ($validated['members'] ?? []) as $index => $member
        ) {
            $memberIdentity =
                $this->personIdentityKey($member);

            if (
                isset(
                    $submittedPeople[$memberIdentity]
                )
            ) {
                return back()
                    ->withErrors([
                        "members.{$index}.first_name" =>
                            'This person has already been registered in this household.',
                    ])
                    ->withInput();
            }

            $submittedPeople[$memberIdentity] =
                "member_{$index}";
        }

        /*
        |--------------------------------------------------------------------------
        | Create Household + Residents + Memberships
        |--------------------------------------------------------------------------
        */

        $householdNumber = DB::transaction(
            function () use ($validated) {

                /*
                |--------------------------------------------------------------------------
                | Generate Household Number
                |--------------------------------------------------------------------------
                */

                do {
                    $number =
                        'HH-' .
                        strtoupper(
                            substr(
                                str_replace(
                                    '-',
                                    '',
                                    uniqid('', true)
                                ),
                                -6
                            )
                        );
                } while (
                    Household::query()
                        ->where(
                            'household_number',
                            $number
                        )
                        ->exists()
                );

                /*
                |--------------------------------------------------------------------------
                | Find/Create Household Head Resident
                |--------------------------------------------------------------------------
                |
                | The household address is always passed into the resident
                | creation/update method.
                |
                */

                $head =
                    $this->findOrCreateResident(
                        $validated['head'],
                        $validated['address']
                    );

                /*
                |--------------------------------------------------------------------------
                | Create Household
                |--------------------------------------------------------------------------
                */

                $household = Household::create([
                    'household_number' =>
                        $number,

                    'address' =>
                        $validated['address'],

                    'purok' =>
                        $validated['purok'],

                    'latitude' =>
                        $validated['latitude'] ?? null,

                    'longitude' =>
                        $validated['longitude'] ?? null,

                    'household_head_id' =>
                        $head->id,

                    'status' =>
                        'Active',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Add Household Head
                |--------------------------------------------------------------------------
                |
                | The household head is also a household member.
                | Therefore, the classifications are stored here.
                |
                */

                HouseholdMember::create([
                    'household_id' =>
                        $household->id,

                    'resident_id' =>
                        $head->id,

                    'relationship' =>
                        'Head',

                    'is_pwd' =>
                        $this->booleanValue(
                            $validated['head']['is_pwd']
                            ?? false
                        ),

                    'is_solo_parent' =>
                        $this->booleanValue(
                            $validated['head']['is_solo_parent']
                            ?? false
                        ),

                    'is_high_school_student' =>
                        $this->booleanValue(
                            $validated['head']['is_high_school_student']
                            ?? false
                        ),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Add Household Members
                |--------------------------------------------------------------------------
                */

                foreach (
                    ($validated['members'] ?? []) as $memberData
                ) {
                    $resident =
                        $this->findOrCreateResident(
                            $memberData,
                            $validated['address']
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent Duplicate Membership
                    |--------------------------------------------------------------------------
                    */

                    $alreadyMember =
                        HouseholdMember::query()
                            ->where(
                                'household_id',
                                $household->id
                            )
                            ->where(
                                'resident_id',
                                $resident->id
                            )
                            ->exists();

                    if ($alreadyMember) {
                        continue;
                    }

                    HouseholdMember::create([
                        'household_id' =>
                            $household->id,

                        'resident_id' =>
                            $resident->id,

                        'relationship' =>
                            $memberData['relationship'],

                        'is_pwd' =>
                            $this->booleanValue(
                                $memberData['is_pwd']
                                ?? false
                            ),

                        'is_solo_parent' =>
                            $this->booleanValue(
                                $memberData['is_solo_parent']
                                ?? false
                            ),

                        'is_high_school_student' =>
                            $this->booleanValue(
                                $memberData['is_high_school_student']
                                ?? false
                            ),
                    ]);
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
    |
    | Household Registration
    |          ↓
    | Resident Individual Record
    |
    | The household address is the source of truth.
    |
    */

    private function findOrCreateResident(
        array $person,
        string $householdAddress
    ): Resident {
        /*
        |--------------------------------------------------------------------------
        | Existing Resident Selected
        |--------------------------------------------------------------------------
        */

        if (!empty($person['resident_id'])) {
            $existingResident =
                Resident::query()
                    ->whereKey(
                        $person['resident_id']
                    )
                    ->first();

            if (!$existingResident) {
                abort(
                    422,
                    'The selected resident could not be found.'
                );
            }

            if (
                $existingResident->status !== 'Active'
            ) {
                abort(
                    422,
                    'The selected resident is not active.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Synchronize Household Address
            |--------------------------------------------------------------------------
            |
            | The household address is authoritative.
            |
            */

            $existingResident->update([
                'address' =>
                    $householdAddress,

                'status' =>
                    'Active',

                /*
                |--------------------------------------------------------------------------
                | FIX (Household registration not saving residency info
                | for EXISTING residents):
                |--------------------------------------------------------------------------
                |
                | Previously only address/status were synchronized here.
                | The household form's modal already collects
                | residency_type, residency_start_year, and
                | residency_year_estimated for an existing resident too
                | (see households/create.blade.php), and those values
                | already pass validation in store() above -- they were
                | just never written to the Resident record on this
                | branch, unlike the "create new resident" branch below,
                | which already saves them correctly.
                |
                | The modal always pre-fills these three fields from the
                | selected resident's current data before the user can
                | edit them (see openExistingMemberModal() in
                | households/create.blade.php), so writing the submitted
                | value back here is safe and matches the "household is
                | the source of truth" intent already used for address
                | above -- it only confirms/updates this same resident's
                | own residency info, nothing else.
                |--------------------------------------------------------------------------
                */

                'residency_type' =>
                    $person['residency_type']
                    ?? $existingResident->residency_type
                    ?? 'Original Resident',

                'residency_start_year' =>
                    !empty(
                        $person['residency_start_year']
                    )
                        ? (int) $person['residency_start_year']
                        : null,

                'residency_year_estimated' =>
                    $this->booleanValue(
                        $person['residency_year_estimated']
                        ?? false
                    ),
            ]);

            return $existingResident->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Person Information
        |--------------------------------------------------------------------------
        */

        $firstName = trim(
            (string) (
                $person['first_name'] ?? ''
            )
        );

        $middleName = trim(
            (string) (
                $person['middle_name'] ?? ''
            )
        );

        $lastName = trim(
            (string) (
                $person['last_name'] ?? ''
            )
        );

        $suffix = trim(
            (string) (
                $person['suffix'] ?? ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Search Existing Resident
        |--------------------------------------------------------------------------
        |
        | Identity is checked before creating a new individual record.
        |
        */

        $query = Resident::query()
            ->where(
                'first_name',
                $firstName
            )
            ->where(
                'last_name',
                $lastName
            );

        /*
        |--------------------------------------------------------------------------
        | Middle Name
        |--------------------------------------------------------------------------
        */

        if ($middleName !== '') {
            $query->where(
                'middle_name',
                $middleName
            );
        } else {
            $query->where(
                function ($q) {
                    $q->whereNull('middle_name')
                        ->orWhere(
                            'middle_name',
                            ''
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Suffix
        |--------------------------------------------------------------------------
        */

        if ($suffix !== '') {
            $query->where(
                'suffix',
                $suffix
            );
        } else {
            $query->where(
                function ($q) {
                    $q->whereNull('suffix')
                        ->orWhere(
                            'suffix',
                            ''
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Birth Date
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $person['birth_date']
            )
        ) {
            $query->whereDate(
                'birth_date',
                Carbon::parse(
                    $person['birth_date']
                )->toDateString()
            );
        } else {
            $query->whereNull(
                'birth_date'
            );
        }

        $resident = $query->first();

        /*
        |--------------------------------------------------------------------------
        | Normalize Email
        |--------------------------------------------------------------------------
        */

        $email = trim(
            (string) (
                $person['email'] ?? ''
            )
        );

        if ($email === '') {
            $email = null;
        } elseif (
            strtoupper($email) === 'N/A'
        ) {
            $email = 'N/A';
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Occupation
        |--------------------------------------------------------------------------
        */

        $occupation =
            $this->resolveOccupation(
                $person
            );

        /*
        |--------------------------------------------------------------------------
        | Existing Resident Found
        |--------------------------------------------------------------------------
        */

        if ($resident) {
            $resident->update([
                'first_name' =>
                    $firstName,

                'middle_name' =>
                    $middleName !== ''
                        ? $middleName
                        : null,

                'last_name' =>
                    $lastName,

                'suffix' =>
                    $suffix !== ''
                        ? $suffix
                        : null,

                'birth_date' =>
                    !empty(
                        $person['birth_date']
                    )
                        ? $person['birth_date']
                        : null,

                'sex' =>
                    $person['sex'] ?? null,

                'civil_status' =>
                    $person['civil_status'] ?? null,

                'contact_number' =>
                    !empty(
                        trim(
                            (string) (
                                $person['contact_number']
                                ?? ''
                            )
                        )
                    )
                        ? trim(
                            (string) (
                                $person['contact_number']
                                ?? ''
                            )
                        )
                        : null,

                'email' =>
                    $email,

                /*
                |--------------------------------------------------------------------------
                | Household Address Is Always Used
                |--------------------------------------------------------------------------
                */

                'address' =>
                    $householdAddress,

                'occupation' =>
                    $occupation,

                'residency_type' =>
                    $person['residency_type']
                    ?? 'Original Resident',

                'residency_start_year' =>
                    !empty(
                        $person['residency_start_year']
                    )
                        ? (int) $person['residency_start_year']
                        : null,

                'residency_year_estimated' =>
                    $this->booleanValue(
                        $person['residency_year_estimated']
                        ?? false
                    ),

                'status' =>
                    'Active',
            ]);

            return $resident->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | Create New Resident
        |--------------------------------------------------------------------------
        */

        return Resident::create([
            'first_name' =>
                $firstName,

            'middle_name' =>
                $middleName !== ''
                    ? $middleName
                    : null,

            'last_name' =>
                $lastName,

            'suffix' =>
                $suffix !== ''
                    ? $suffix
                    : null,

            'birth_date' =>
                !empty(
                    $person['birth_date']
                )
                    ? $person['birth_date']
                    : null,

            'sex' =>
                $person['sex'] ?? null,

            'civil_status' =>
                $person['civil_status']
                ?? null,

            'contact_number' =>
                !empty(
                    trim(
                        (string) (
                            $person['contact_number']
                            ?? ''
                        )
                    )
                )
                    ? trim(
                        (string) (
                            $person['contact_number']
                            ?? ''
                        )
                    )
                    : null,

            'email' =>
                $email,

            /*
            |--------------------------------------------------------------------------
            | Household Address Is the Source of Truth
            |--------------------------------------------------------------------------
            */

            'address' =>
                $householdAddress,

            'occupation' =>
                $occupation,

            'residency_type' =>
                $person['residency_type']
                ?? 'Original Resident',

            'residency_start_year' =>
                !empty(
                    $person['residency_start_year']
                )
                    ? (int) $person['residency_start_year']
                    : null,

            'residency_year_estimated' =>
                $this->booleanValue(
                    $person['residency_year_estimated']
                    ?? false
                ),

            'status' =>
                'Active',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Civil Status Options
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

    /*
    |--------------------------------------------------------------------------
    | Occupation Options
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Resolve Occupation
    |--------------------------------------------------------------------------
    |
    | If "Other" is selected, the specified occupation becomes the actual
    | stored occupation value.
    |
    */

    private function resolveOccupation(
        array $person
    ): ?string {
        $occupation = trim(
            (string) (
                $person['occupation'] ?? ''
            )
        );

        if ($occupation === '') {
            return null;
        }

        if ($occupation !== 'Other') {
            return $occupation;
        }

        $other = trim(
            (string) (
                $person['occupation_other'] ?? ''
            )
        );

        return $other !== ''
            ? $other
            : 'Other';
    }

    /*
    |--------------------------------------------------------------------------
    | Boolean Value Helper
    |--------------------------------------------------------------------------
    */

    private function booleanValue(
        mixed $value
    ): bool {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resident Identity Key
    |--------------------------------------------------------------------------
    */

    private function personIdentityKey(
        array $person
    ): string {
        return strtolower(
            trim(
                (string) (
                    $person['first_name']
                    ?? ''
                )
            )
            . '|'
            .
            trim(
                (string) (
                    $person['middle_name']
                    ?? ''
                )
            )
            . '|'
            .
            trim(
                (string) (
                    $person['last_name']
                    ?? ''
                )
            )
            . '|'
            .
            trim(
                (string) (
                    $person['suffix']
                    ?? ''
                )
            )
            . '|'
            .
            trim(
                (string) (
                    $person['birth_date']
                    ?? ''
                )
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resident Full Name
    |--------------------------------------------------------------------------
    */

    private function residentFullName(
        Resident $resident
    ): string {
        return trim(
            $resident->first_name
            . ' '
            . (
                $resident->middle_name
                    ? $resident->middle_name . ' '
                    : ''
            )
            . $resident->last_name
            . (
                $resident->suffix
                    ? ' ' . $resident->suffix
                    : ''
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Household
    |--------------------------------------------------------------------------
    */

    public function show(
        Household $household
    ): View {
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

    public function edit(
        Household $household
    ): View {
        $this->authorizeHouseholdManagement();

        $household->load([
            'householdHead',
            'members.resident',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Active Residents
        |--------------------------------------------------------------------------
        */

        $residents = Resident::query()
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

        return view(
            'households.edit',
            compact(
                'household',
                'residents'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Household
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Household $household
    ): RedirectResponse {
        $this->authorizeHouseholdManagement();

        $validated = $request->validate([
            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'purok' => [
                'required',
                'string',
                'max:100',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'household_head_id' => [
                'required',
                'integer',
                'exists:residents,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Coordinates Must Be Supplied Together
        |--------------------------------------------------------------------------
        */

        if (
            ($validated['latitude'] ?? null) !== null
            && ($validated['longitude'] ?? null) === null
        ) {
            return back()
                ->withErrors([
                    'longitude' =>
                        'Longitude is required when latitude is provided.',
                ])
                ->withInput();
        }

        if (
            ($validated['longitude'] ?? null) !== null
            && ($validated['latitude'] ?? null) === null
        ) {
            return back()
                ->withErrors([
                    'latitude' =>
                        'Latitude is required when longitude is provided.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Household Head
        |--------------------------------------------------------------------------
        */

        $householdHead =
            Resident::query()
                ->whereKey(
                    $validated['household_head_id']
                )
                ->where(
                    'status',
                    'Active'
                )
                ->first();

        if (!$householdHead) {
            return back()
                ->withErrors([
                    'household_head_id' =>
                        'The selected household head is not an active resident.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Update Household
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $validated,
                $household
            ) {
                /*
                |--------------------------------------------------------------------------
                | Remember Current Head
                |--------------------------------------------------------------------------
                */

                $oldHeadId =
                    $household->household_head_id;

                $newHeadId =
                    $validated['household_head_id'];

                /*
                |--------------------------------------------------------------------------
                | Update Household
                |--------------------------------------------------------------------------
                */

                $household->update([
                    'address' =>
                        $validated['address'],

                    'purok' =>
                        $validated['purok'],

                    'latitude' =>
                        $validated['latitude'] ?? null,

                    'longitude' =>
                        $validated['longitude'] ?? null,

                    'household_head_id' =>
                        $newHeadId,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Synchronize Household Address
                |--------------------------------------------------------------------------
                |
                | All existing household members inherit the updated
                | household address.
                |
                */

                $memberResidentIds =
                    HouseholdMember::query()
                        ->where(
                            'household_id',
                            $household->id
                        )
                        ->pluck('resident_id');

                if ($memberResidentIds->isNotEmpty()) {
                    Resident::query()
                        ->whereIn(
                            'id',
                            $memberResidentIds
                        )
                        ->update([
                            'address' =>
                                $validated['address'],
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Ensure New Head Has Household Address
                |--------------------------------------------------------------------------
                */

                Resident::query()
                    ->whereKey($newHeadId)
                    ->update([
                        'address' =>
                            $validated['address'],
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Find Existing Membership of New Head
                |--------------------------------------------------------------------------
                */

                $newHeadMembership =
                    HouseholdMember::query()
                        ->where(
                            'household_id',
                            $household->id
                        )
                        ->where(
                            'resident_id',
                            $newHeadId
                        )
                        ->first();

                /*
                |--------------------------------------------------------------------------
                | Remove Head Status From Previous Head
                |--------------------------------------------------------------------------
                */

                if (
                    $oldHeadId
                    && (int) $oldHeadId
                        !== (int) $newHeadId
                ) {
                    HouseholdMember::query()
                        ->where(
                            'household_id',
                            $household->id
                        )
                        ->where(
                            'resident_id',
                            $oldHeadId
                        )
                        ->where(
                            'relationship',
                            'Head'
                        )
                        ->update([
                            'relationship' =>
                                'Former Head',
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Promote Existing Member
                |--------------------------------------------------------------------------
                */

                if ($newHeadMembership) {
                    $newHeadMembership->update([
                        'relationship' =>
                            'Head',
                    ]);
                } else {
                    HouseholdMember::create([
                        'household_id' =>
                            $household->id,

                        'resident_id' =>
                            $newHeadId,

                        'relationship' =>
                            'Head',

                        'is_pwd' =>
                            false,

                        'is_solo_parent' =>
                            false,

                        'is_high_school_student' =>
                            false,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Safety: Remove Any Other Head Relationship
                |--------------------------------------------------------------------------
                */

                HouseholdMember::query()
                    ->where(
                        'household_id',
                        $household->id
                    )
                    ->where(
                        'relationship',
                        'Head'
                    )
                    ->where(
                        'resident_id',
                        '!=',
                        $newHeadId
                    )
                    ->update([
                        'relationship' =>
                            'Former Head',
                    ]);
            }
        );

        return redirect()
            ->route(
                'households.show',
                $household
            )
            ->with(
                'success',
                "Household {$household->household_number} was successfully updated."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Archive Household
    |--------------------------------------------------------------------------
    */

    public function archive(
        Household $household
    ): RedirectResponse {
        $this->authorizeHouseholdManagement();

        $household->update([
            'status' =>
                'Archived',
        ]);

        return redirect()
            ->route('households.index')
            ->with(
                'success',
                "Household {$household->household_number} was archived."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Restore Household
    |--------------------------------------------------------------------------
    */

    public function restore(
        Household $household
    ): RedirectResponse {
        $this->authorizeHouseholdManagement();

        $household->update([
            'status' =>
                'Active',
        ]);

        return redirect()
            ->route('households.index')
            ->with(
                'success',
                "Household {$household->household_number} was restored."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Household
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Household $household
    ): RedirectResponse {
        $this->authorizeHouseholdManagement();

        $householdNumber =
            $household->household_number;

        $household->delete();

        return redirect()
            ->route('households.index')
            ->with(
                'success',
                "Household {$householdNumber} was permanently deleted."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Add Existing Household Member
    |--------------------------------------------------------------------------
    */

    public function addMember(
        Request $request,
        Household $household
    ): RedirectResponse {
        $this->authorizeHouseholdManagement();

        $validated = $request->validate([
            'resident_id' => [
                'required',
                'integer',
                'exists:residents,id',
            ],

            'relationship' => [
                'required',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Member Classifications
            |--------------------------------------------------------------------------
            */

            'is_pwd' => [
                'nullable',
                'boolean',
            ],

            'is_solo_parent' => [
                'nullable',
                'boolean',
            ],

            'is_high_school_student' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify Resident
        |--------------------------------------------------------------------------
        */

        $resident =
            Resident::query()
                ->whereKey(
                    $validated['resident_id']
                )
                ->where(
                    'status',
                    'Active'
                )
                ->first();

        if (!$resident) {
            return back()
                ->withErrors([
                    'resident_id' =>
                        'The selected resident is not active.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Household Head From Being Added As Another Member
        |--------------------------------------------------------------------------
        */

        if (
            $household->household_head_id
            && (int) $household->household_head_id
                === (int) $resident->id
        ) {
            return back()
                ->withErrors([
                    'resident_id' =>
                        'This resident is already the household head.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Membership
        |--------------------------------------------------------------------------
        */

        $alreadyMember =
            HouseholdMember::query()
                ->where(
                    'household_id',
                    $household->id
                )
                ->where(
                    'resident_id',
                    $resident->id
                )
                ->exists();

        if ($alreadyMember) {
            return back()
                ->withErrors([
                    'resident_id' =>
                        'This resident is already a member of this household.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Synchronize Resident Address
        |--------------------------------------------------------------------------
        |
        | The household address is the source of truth.
        |
        */

        $resident->update([
            'address' =>
                $household->address,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Add Membership
        |--------------------------------------------------------------------------
        */

        HouseholdMember::create([
            'household_id' =>
                $household->id,

            'resident_id' =>
                $resident->id,

            'relationship' =>
                $validated['relationship'],

            'is_pwd' =>
                $this->booleanValue(
                    $validated['is_pwd']
                    ?? false
                ),

            'is_solo_parent' =>
                $this->booleanValue(
                    $validated['is_solo_parent']
                    ?? false
                ),

            'is_high_school_student' =>
                $this->booleanValue(
                    $validated['is_high_school_student']
                    ?? false
                ),
        ]);

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

    public function removeMember(
        Household $household,
        Resident $resident
    ): RedirectResponse {
        $this->authorizeHouseholdManagement();

        $member =
            HouseholdMember::query()
                ->where(
                    'household_id',
                    $household->id
                )
                ->where(
                    'resident_id',
                    $resident->id
                )
                ->first();

        if (!$member) {
            return back()
                ->withErrors([
                    'resident' =>
                        'The resident is not a member of this household.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Removing Head
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(
                (string) $member->relationship
            ) === 'head'
        ) {
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
    |
    | Captain:
    |     Full household management.
    |
    | Secretary:
    |     Full household management.
    |
    | Treasurer:
    |     View only.
    |
    |--------------------------------------------------------------------------
    */

    private function authorizeHouseholdManagement(): void
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | User Must Be Authenticated
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            abort(
                403,
                'You must be logged in to perform this action.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Account Must Be Active
        |--------------------------------------------------------------------------
        */

        if ($user->status !== 'active') {
            abort(
                403,
                'Your account is inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Captain and Secretary Can Manage Households
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === 'captain'
            || $user->role === 'secretary'
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Other Roles Cannot Modify Household Records
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'You do not have permission to modify household records.'
        );
    }
}
