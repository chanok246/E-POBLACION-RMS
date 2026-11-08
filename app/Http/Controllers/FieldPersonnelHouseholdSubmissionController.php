<?php

namespace App\Http\Controllers;

use App\Models\FieldPersonnelHouseholdSubmission;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Resident;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FieldPersonnelHouseholdSubmissionController extends Controller
{
    /**
     * Display pending and previously reviewed Field Personnel submissions.
     */
    public function index(Request $request): View
    {
        $query = FieldPersonnelHouseholdSubmission::query()
            ->with(['fieldPersonnelAccount', 'reviewer', 'approvedHousehold'])
            ->latest('submitted_at');

        $status = $request->query('status');

        if (in_array($status, [
            FieldPersonnelHouseholdSubmission::STATUS_PENDING,
            FieldPersonnelHouseholdSubmission::STATUS_APPROVED,
            FieldPersonnelHouseholdSubmission::STATUS_REJECTED,
        ], true)) {
            $query->where('status', $status);
        }

        return view('admin.field-personnel-submissions.index', [
            'submissions' => $query->paginate(15)->withQueryString(),
            'pendingCount' => FieldPersonnelHouseholdSubmission::query()
                ->pending()
                ->count(),
            'approvedCount' => FieldPersonnelHouseholdSubmission::query()
                ->approved()
                ->count(),
            'rejectedCount' => FieldPersonnelHouseholdSubmission::query()
                ->rejected()
                ->count(),
            'selectedStatus' => $status,
        ]);
    }

    /**
     * Display one submission and possible existing resident matches.
     */
    public function show(
        FieldPersonnelHouseholdSubmission $submission
    ): View {
        $submission->load([
            'fieldPersonnelAccount',
            'reviewer',
            'approvedHousehold',
        ]);

        $possibleMatches = $this->findPossibleResidentMatches($submission);

        return view('admin.field-personnel-submissions.show', [
            'submission' => $submission,
            'possibleMatches' => $possibleMatches,
        ]);
    }

    /**
     * Approve a pending submission and create official RMS records.
     *
     * The submission is locked and checked again inside the transaction.
     * This prevents two reviewers from approving the same submission.
     */
    public function approve(
        Request $request,
        FieldPersonnelHouseholdSubmission $submission
    ): RedirectResponse {
        $reviewer = $request->user();

        abort_unless($reviewer, 401);

        $approvedHouseholdId = DB::transaction(function () use (
            $submission,
            $reviewer
        ) {
            $lockedSubmission = FieldPersonnelHouseholdSubmission::query()
                ->whereKey($submission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedSubmission->isPending()) {
                throw ValidationException::withMessages([
                    'submission' => 'This submission has already been reviewed. Only pending submissions can be approved.',
                ]);
            }

            $payload = $lockedSubmission->payload;

            if (!is_array($payload)) {
                throw ValidationException::withMessages([
                    'submission' => 'The submission data is invalid or unreadable.',
                ]);
            }

            $householdData = $payload['household'] ?? null;
            $headData = $payload['household_head'] ?? null;
            $membersData = $payload['members'] ?? [];

            if (
                !is_array($householdData)
                || !is_array($headData)
                || !is_array($membersData)
            ) {
                throw ValidationException::withMessages([
                    'submission' => 'The household submission is incomplete.',
                ]);
            }

            $this->validateSubmissionForApproval(
                $householdData,
                $headData,
                $membersData
            );

            /*
             * Do not silently update or merge an existing resident.
             * A matching identity must be reviewed separately first.
             */
            $matches = $this->findPossibleResidentMatches($lockedSubmission);

            if ($matches->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'submission' => 'One or more people in this submission match existing resident records. Review the possible matches before approving to avoid duplicate or incorrect resident records.',
                ]);
            }

            $address = trim($householdData['address']);
            $householdNumber = $this->generateHouseholdNumber();

            /*
             * Create the household head as a new official resident.
             */
            $head = $this->createOfficialResident(
                $headData,
                $address
            );

            $household = new Household([
                'household_number' => $householdNumber,
                'address' => $address,
                'purok' => trim($householdData['purok']),
                'latitude' => $householdData['latitude'] ?? null,
                'longitude' => $householdData['longitude'] ?? null,
                'household_head_id' => $head->id,
                'status' => 'Active',
            ]);

            $household->forceFill(
                $this->existingColumnsOnly(
                    'households',
                    [
                        'is_low_income' => false,
                        'is_4ps_beneficiary' => false,
                    ]
                )
            );

            $household->save();

            $this->createOfficialMembership(
                $household,
                $head,
                'Head'
            );

            /*
             * Create each additional resident and membership.
             * No classification is assumed because the Field Collection
             * form does not collect those classifications.
             */
            foreach ($membersData as $memberData) {
                $resident = $this->createOfficialResident(
                    $memberData,
                    $address
                );

                $this->createOfficialMembership(
                    $household,
                    $resident,
                    trim($memberData['relationship'])
                );
            }

            /*
             * Mark the submission approved only after all official records
             * have been successfully created in this transaction.
             */
            $lockedSubmission->status =
                FieldPersonnelHouseholdSubmission::STATUS_APPROVED;

            $lockedSubmission->reviewed_by = $reviewer->id;
            $lockedSubmission->reviewed_at = now();
            $lockedSubmission->approved_household_id = $household->id;
            $lockedSubmission->rejection_reason = null;
            $lockedSubmission->save();

            return $household->id;
        }, 3);

        return redirect()
            ->route('admin.field-personnel-submissions.show', $submission)
            ->with(
                'success',
                'Submission approved successfully. Official household records have been created.'
            );
    }

    /**
     * Reject a pending submission while preserving its complete history.
     */
    public function reject(
        Request $request,
        FieldPersonnelHouseholdSubmission $submission
    ): RedirectResponse {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejecting this submission.',
            'rejection_reason.min' => 'The rejection reason must contain at least 5 characters.',
            'rejection_reason.max' => 'The rejection reason must not exceed 2,000 characters.',
        ]);

        $reviewer = $request->user();

        abort_unless($reviewer, 401);

        DB::transaction(function () use (
            $submission,
            $reviewer,
            $validated
        ) {
            $lockedSubmission = FieldPersonnelHouseholdSubmission::query()
                ->whereKey($submission->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedSubmission->isPending()) {
                throw ValidationException::withMessages([
                    'submission' => 'This submission has already been reviewed. Only pending submissions can be rejected.',
                ]);
            }

            $lockedSubmission->status =
                FieldPersonnelHouseholdSubmission::STATUS_REJECTED;

            $lockedSubmission->reviewed_by = $reviewer->id;
            $lockedSubmission->reviewed_at = now();
            $lockedSubmission->rejection_reason =
                trim($validated['rejection_reason']);

            $lockedSubmission->save();
        }, 3);

        return redirect()
            ->route('admin.field-personnel-submissions.show', $submission)
            ->with(
                'success',
                'Submission rejected. The submission and rejection reason have been retained in the system.'
            );
    }

    /**
     * Validate the stored submission again before creating official records.
     * This is separate from Field Personnel request validation because the
     * approval action must not blindly trust previously stored payload data.
     */
    private function validateSubmissionForApproval(
        array $household,
        array $head,
        array $members
    ): void {
        $errors = [];

        if (
            empty($household['purok'])
            || !is_string($household['purok'])
            || mb_strlen(trim($household['purok'])) > 100
        ) {
            $errors['submission'] =
                'The household purok is missing or invalid.';
        }

        if (
            empty($household['address'])
            || !is_string($household['address'])
            || mb_strlen(trim($household['address'])) > 255
        ) {
            $errors['submission'] =
                'The household address is missing or invalid.';
        }

        $latitude = $household['latitude'] ?? null;
        $longitude = $household['longitude'] ?? null;

        if (($latitude === null) !== ($longitude === null)) {
            $errors['submission'] =
                'The household GPS coordinates are incomplete.';
        }

        if (
            $latitude !== null
            && (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)
        ) {
            $errors['submission'] = 'The household GPS latitude is invalid.';
        }

        if (
            $longitude !== null
            && (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)
        ) {
            $errors['submission'] = 'The household GPS longitude is invalid.';
        }

        if (empty($head['first_name']) || empty($head['last_name'])) {
            $errors['submission'] =
                'The household head information is incomplete.';
        }

        if (empty($head['birth_date']) || empty($head['sex'])) {
            $errors['submission'] =
                'The household head birth date or sex is missing.';
        }

        if (!in_array($head['residency_type'] ?? null, [
            'Permanent',
            'Temporary',
        ], true)) {
            $errors['submission'] =
                'The household head residency type is invalid.';
        }

        $people = array_merge([$head], $members);

        foreach ($people as $person) {
            if (
                !is_array($person)
                || empty($person['first_name'])
                || empty($person['last_name'])
                || empty($person['birth_date'])
                || empty($person['sex'])
            ) {
                $errors['submission'] =
                    'One or more household member records are incomplete.';
                break;
            }

            if (!in_array($person['sex'], ['Male', 'Female'], true)) {
                $errors['submission'] =
                    'One or more household member sex values are invalid.';
                break;
            }

            if (!in_array($person['residency_type'] ?? null, [
                'Permanent',
                'Temporary',
            ], true)) {
                $errors['submission'] =
                    'One or more household member residency types are invalid.';
                break;
            }

            if (($person['residency_type'] ?? null) === 'Temporary') {
                $year = $person['residency_start_year'] ?? null;

                if (
                    !is_numeric($year)
                    || (int) $year < 1900
                    || (int) $year > now()->year
                ) {
                    $errors['submission'] =
                        'Every Temporary resident must have a valid residency start year.';
                    break;
                }
            }

            if (!empty($person['contact_number'])) {
                if (
                    !is_string($person['contact_number'])
                    || preg_match(
                        '/^\+639\d{9}$/',
                        $person['contact_number']
                    ) !== 1
                ) {
                    $errors['submission'] =
                        'One or more Philippine mobile numbers are invalid.';
                    break;
                }
            }
        }

        foreach ($members as $member) {
            if (
                !is_array($member)
                || empty($member['relationship'])
                || !is_string($member['relationship'])
            ) {
                $errors['submission'] =
                    'One or more household member relationships are missing.';
                break;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Create a new official resident.
     *
     * Existing resident records are never modified by this method.
     */
    private function createOfficialResident(
        array $person,
        string $householdAddress
    ): Resident {
        $residencyType = $person['residency_type'] === 'Temporary'
            ? 'Migrant Resident'
            : 'Original Resident';

        $email = trim((string) ($person['email'] ?? ''));

        if ($email === '') {
            $email = null;
        }

        $contactNumber = trim(
            (string) ($person['contact_number'] ?? '')
        );

        return Resident::create([
            'first_name' => trim($person['first_name']),
            'middle_name' => $this->nullableTrim($person['middle_name'] ?? null),
            'last_name' => trim($person['last_name']),
            'suffix' => $this->nullableTrim($person['suffix'] ?? null),
            'birth_date' => $person['birth_date'],
            'sex' => $person['sex'],
            'civil_status' => $person['civil_status'] ?? null,
            'contact_number' => $contactNumber !== ''
                ? $contactNumber
                : null,
            'email' => $email,
            'address' => $householdAddress,
            'residency_type' => $residencyType,
            'residency_start_year' => !empty($person['residency_start_year'])
                ? (int) $person['residency_start_year']
                : null,
            'residency_year_estimated' => false,
            'occupation' => $this->nullableTrim($person['occupation'] ?? null),
            'status' => 'Active',
        ]);
    }

    /**
     * Create a household membership with only columns present in the
     * installed database. Uncollected classifications default to false.
     */
    private function createOfficialMembership(
        Household $household,
        Resident $resident,
        string $relationship
    ): HouseholdMember {
        $attributes = [
            'household_id' => $household->id,
            'resident_id' => $resident->id,
            'relationship' => $relationship,
            'is_pwd' => false,
            'is_senior_citizen' => false,
            'is_solo_parent' => false,
            'is_student' => false,
            'education_level' => null,
            'is_high_school' => false,
            'is_high_school_student' => false,
        ];

        return HouseholdMember::forceCreate(
            $this->existingColumnsOnly(
                'household_members',
                $attributes
            )
        );
    }

    /**
     * Find possible exact identity matches for review.
     *
     * A matching resident is not automatically linked or updated.
     */
    private function findPossibleResidentMatches(
        FieldPersonnelHouseholdSubmission $submission
    ) {
        $payload = $submission->payload;

        if (!is_array($payload)) {
            return collect();
        }

        $people = array_merge(
            isset($payload['household_head']) && is_array($payload['household_head'])
                ? [$payload['household_head']]
                : [],
            isset($payload['members']) && is_array($payload['members'])
                ? $payload['members']
                : []
        );

        $matches = collect();

        foreach ($people as $person) {
            if (
                empty($person['first_name'])
                || empty($person['last_name'])
                || empty($person['birth_date'])
            ) {
                continue;
            }

            $query = Resident::query()
                ->whereRaw(
                    'LOWER(first_name) = ?',
                    [mb_strtolower(trim($person['first_name']))]
                )
                ->whereRaw(
                    'LOWER(last_name) = ?',
                    [mb_strtolower(trim($person['last_name']))]
                )
                ->whereDate('birth_date', $person['birth_date']);

            $middleName = trim((string) ($person['middle_name'] ?? ''));
            $suffix = trim((string) ($person['suffix'] ?? ''));

            if ($middleName !== '') {
                $query->whereRaw(
                    'LOWER(COALESCE(middle_name, "")) = ?',
                    [mb_strtolower($middleName)]
                );
            } else {
                $query->where(function ($builder) {
                    $builder->whereNull('middle_name')
                        ->orWhere('middle_name', '');
                });
            }

            if ($suffix !== '') {
                $query->whereRaw(
                    'LOWER(COALESCE(suffix, "")) = ?',
                    [mb_strtolower($suffix)]
                );
            } else {
                $query->where(function ($builder) {
                    $builder->whereNull('suffix')
                        ->orWhere('suffix', '');
                });
            }

            $personMatches = $query->limit(10)->get();

            if ($personMatches->isNotEmpty()) {
                $matches->push([
                    'submitted_person' => trim(implode(' ', array_filter([
                        $person['first_name'] ?? '',
                        $person['middle_name'] ?? '',
                        $person['last_name'] ?? '',
                        $person['suffix'] ?? '',
                    ]))),
                    'residents' => $personMatches,
                ]);
            }
        }

        return $matches;
    }

    /**
     * Generate a unique household number.
     */
    private function generateHouseholdNumber(): string
    {
        do {
            $number = 'HH-' . Str::upper(Str::random(6));
        } while (
            Household::query()
                ->where('household_number', $number)
                ->exists()
        );

        return $number;
    }

    /**
     * Return only columns that exist in the installed database.
     */
    private function existingColumnsOnly(
        string $table,
        array $attributes
    ): array {
        return collect($attributes)
            ->filter(function ($value, $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->all();
    }

    private function nullableTrim(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
