<?php

namespace App\Http\Controllers;

use App\Models\FieldPersonnelHouseholdSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FieldPersonnelController extends Controller
{
    /**
     * Philippine mobile number stored format:
     * +639XXXXXXXXX
     */
    private const PH_MOBILE_PATTERN = '/^\+639\d{9}$/';

    public function index(Request $request): View
    {
        $personnel = $request->user('field_personnel');

        abort_unless($personnel, 401);

        $recentSubmissions = FieldPersonnelHouseholdSubmission::query()
            ->where('field_personnel_account_id', $personnel->id)
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return view('field-personnel.home', [
            'personnel' => $personnel,
            'tasks' => collect(),
            'recentSubmissions' => $recentSubmissions,
        ]);
    }

    public function createHousehold(): View
    {
        return view('field-personnel.households.create');
    }

    /**
     * Receive a household collected by Field Personnel.
     *
     * IMPORTANT:
     * This method only creates a pending submission.
     * It does not create or update official residents, households,
     * or household_members records.
     */
    public function storeHousehold(Request $request): RedirectResponse
    {
        $personnel = $request->user('field_personnel');

        abort_unless($personnel, 401);

        /*
         * Normalize phone numbers before validation.
         * The mobile form accepts 9XXXXXXXXX because +63 is displayed
         * separately. The database submission payload stores +639XXXXXXXXX.
         */
        $input = $request->all();

        if (isset($input['household_head']['contact_number'])) {
            $input['household_head']['contact_number'] =
                $this->normalizeMobileNumber(
                    $input['household_head']['contact_number']
                );
        }

        if (isset($input['members']) && is_array($input['members'])) {
            foreach ($input['members'] as $index => $member) {
                if (isset($member['contact_number'])) {
                    $input['members'][$index]['contact_number'] =
                        $this->normalizeMobileNumber(
                            $member['contact_number']
                        );
                }
            }
        }

        $request->merge($input);

        $validated = Validator::make($request->all(), [
            'household.purok' => [
                'required',
                'string',
                'max:100',
            ],
            'household.address' => [
                'required',
                'string',
                'max:255',
            ],
            'household.latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'household.longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'household.gps_accuracy_meters' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100000',
            ],
            'household.gps_captured_at' => [
                'nullable',
                'date',
            ],

            'household_head.first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'household_head.middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'household_head.last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'household_head.suffix' => [
                'nullable',
                'string',
                'max:30',
            ],
            'household_head.birth_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'household_head.sex' => [
                'required',
                'in:Male,Female',
            ],
            'household_head.civil_status' => [
                'required',
                'in:Single,Married,Widowed,Separated,Annulled',
            ],
            'household_head.contact_number' => [
                'nullable',
                'string',
                'regex:' . self::PH_MOBILE_PATTERN,
            ],
            'household_head.email' => [
                'nullable',
                'string',
                'max:255',
                'email',
            ],
            'household_head.residency_type' => [
                'required',
                'in:Permanent,Temporary',
            ],
            'household_head.residency_start_year' => [
                'nullable',
                'integer',
                'digits:4',
                'min:1900',
                'max:' . now()->year,
            ],
            'household_head.occupation' => [
                'nullable',
                'string',
                'max:150',
            ],

            'members' => [
                'nullable',
                'array',
                'max:50',
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
                'required',
                'date',
                'before_or_equal:today',
            ],
            'members.*.sex' => [
                'required',
                'in:Male,Female',
            ],
            'members.*.civil_status' => [
                'required',
                'in:Single,Married,Widowed,Separated,Annulled',
            ],
            'members.*.relationship' => [
                'required',
                'string',
                'max:100',
            ],
            'members.*.contact_number' => [
                'nullable',
                'string',
                'regex:' . self::PH_MOBILE_PATTERN,
            ],
            'members.*.email' => [
                'nullable',
                'string',
                'max:255',
                'email',
            ],
            'members.*.residency_type' => [
                'required',
                'in:Permanent,Temporary',
            ],
            'members.*.residency_start_year' => [
                'nullable',
                'integer',
                'digits:4',
                'min:1900',
                'max:' . now()->year,
            ],
            'members.*.occupation' => [
                'nullable',
                'string',
                'max:150',
            ],
        ], [
            'household.purok.required' => 'Please select or enter the household purok.',
            'household.address.required' => 'The household address is required.',
            'household_head.first_name.required' => 'The household head first name is required.',
            'household_head.last_name.required' => 'The household head last name is required.',
            'household_head.birth_date.required' => 'The household head birth date is required.',
            'household_head.residency_type.required' => 'Please specify the household head residency type.',
            'members.*.relationship.required' => 'Please specify each household member relationship.',
            '*.contact_number.regex' => 'Contact numbers must be valid Philippine mobile numbers.',
        ])->validate();

        /*
         * The two coordinates must either both be present or both be blank.
         */
        $latitude = $validated['household']['latitude'] ?? null;
        $longitude = $validated['household']['longitude'] ?? null;

        if (($latitude === null) !== ($longitude === null)) {
            throw ValidationException::withMessages([
                'household.latitude' =>
                    'GPS latitude and longitude must both be provided.',
            ]);
        }

        /*
         * Confirmed residency mapping:
         * Permanent -> Original Resident
         * Temporary -> Migrant Resident
         *
         * A Temporary resident must have a valid residency start year.
         */
        $people = array_merge(
            [$validated['household_head']],
            $validated['members'] ?? []
        );

        foreach ($people as $index => $person) {
            if (
                ($person['residency_type'] ?? null) === 'Temporary'
                && empty($person['residency_start_year'])
            ) {
                $field = $index === 0
                    ? 'household_head.residency_start_year'
                    : 'members.' . ($index - 1) . '.residency_start_year';

                throw ValidationException::withMessages([
                    $field => 'Residency start year is required for Temporary residents.',
                ]);
            }
        }

        /*
         * Prevent duplicate persons within the same household submission.
         * Existing residents are not searched or modified here.
         */
        $identityKeys = [];

        foreach ($people as $index => $person) {
            $identity = implode('|', [
                mb_strtolower(trim($person['first_name'] ?? '')),
                mb_strtolower(trim($person['middle_name'] ?? '')),
                mb_strtolower(trim($person['last_name'] ?? '')),
                mb_strtolower(trim($person['suffix'] ?? '')),
                $person['birth_date'] ?? '',
            ]);

            if (isset($identityKeys[$identity])) {
                $field = $index === 0
                    ? 'household_head.first_name'
                    : 'members.' . ($index - 1) . '.first_name';

                throw ValidationException::withMessages([
                    $field => 'A duplicate person was found in this household submission.',
                ]);
            }

            $identityKeys[$identity] = true;
        }

        /*
         * Store only validated data. No official RMS record is created here.
         */
        $payload = [
            'household' => [
                'purok' => trim($validated['household']['purok']),
                'address' => trim($validated['household']['address']),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'gps_accuracy_meters' =>
                    $validated['household']['gps_accuracy_meters'] ?? null,
                'gps_captured_at' =>
                    $validated['household']['gps_captured_at'] ?? null,
            ],
            'household_head' => $validated['household_head'],
            'members' => $validated['members'] ?? [],
        ];

        $reference = $this->generateSubmissionReference();

        $personnelName = $this->getPersonnelDisplayName($personnel);

        FieldPersonnelHouseholdSubmission::create([
            'submission_reference' => $reference,
            'field_personnel_account_id' => $personnel->id,
            'submitted_by_name' => $personnelName,
            'submitted_by_position' =>
                $personnel->getAttribute('position')
                ?? $personnel->getAttribute('job_title'),
            'payload' => $payload,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'gps_accuracy_meters' =>
                $validated['household']['gps_accuracy_meters'] ?? null,
            'gps_captured_at' =>
                $validated['household']['gps_captured_at'] ?? null,
            'status' => FieldPersonnelHouseholdSubmission::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('field-personnel.submissions.index')
            ->with(
                'success',
                'Household information submitted successfully for verification. Reference: '
                . $reference
            );
    }

    public function verifyVoucher(): View
    {
        return view('field-personnel.vouchers.verify');
    }

    public function submissions(Request $request): View
    {
        $personnel = $request->user('field_personnel');

        abort_unless($personnel, 401);

        $submissions = FieldPersonnelHouseholdSubmission::query()
            ->where('field_personnel_account_id', $personnel->id)
            ->latest('submitted_at')
            ->paginate(15);

        return view('field-personnel.submissions.index', [
            'submissions' => $submissions,
        ]);
    }

    public function tasks(Request $request): View
    {
        abort_unless($request->user('field_personnel'), 401);

        return view('field-personnel.tasks.index', [
            'tasks' => collect(),
        ]);
    }

    /**
     * Normalize accepted Philippine mobile number formats.
     */
    private function normalizeMobileNumber(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Form input: 9XXXXXXXXX
        if (preg_match('/^9\d{9}$/', $value) === 1) {
            return '+63' . $value;
        }

        // International format without plus: 639XXXXXXXXX
        if (preg_match('/^639\d{9}$/', $value) === 1) {
            return '+' . $value;
        }

        // Local format: 09XXXXXXXXX
        if (preg_match('/^09\d{9}$/', $value) === 1) {
            return '+63' . substr($value, 1);
        }

        return $value;
    }

    /**
     * Generate a unique human-readable submission reference.
     */
    private function generateSubmissionReference(): string
    {
        do {
            $reference = 'FP-HH-' . now()->format('Ymd') . '-'
                . Str::upper(Str::random(6));
        } while (
            FieldPersonnelHouseholdSubmission::query()
                ->where('submission_reference', $reference)
                ->exists()
        );

        return $reference;
    }

    /**
     * Resolve the display name without depending on a single optional
     * account-name column.
     */
    private function getPersonnelDisplayName(object $personnel): string
    {
        $firstName = trim((string) ($personnel->getAttribute('first_name') ?? ''));
        $middleName = trim((string) ($personnel->getAttribute('middle_name') ?? ''));
        $lastName = trim((string) ($personnel->getAttribute('last_name') ?? ''));

        $fullName = trim(implode(' ', array_filter([
            $firstName,
            $middleName,
            $lastName,
        ])));

        if ($fullName !== '') {
            return $fullName;
        }

        return (string) (
            $personnel->getAttribute('name')
            ?? $personnel->getAttribute('username')
            ?? 'Field Personnel'
        );
    }
}
