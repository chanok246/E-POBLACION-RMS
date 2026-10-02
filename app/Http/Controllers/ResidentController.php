<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    /**
     * Display a listing of residents.
     *
     * Captain, Secretary, and Treasurer can view residents.
     */
    public function index(Request $request)
    {
        $query = Resident::query();

        /*
        |--------------------------------------------------------------------------
        | Search by Name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
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
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Sex
        |--------------------------------------------------------------------------
        */

        if ($request->filled('sex')) {
            $query->where(
                'sex',
                $request->sex
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Residents
        |--------------------------------------------------------------------------
        */

        $residents = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'residents.index',
            compact('residents')
        );
    }

    /**
     * Show the form for creating a new resident.
     *
     * Captain and Secretary only.
     */
    public function create()
    {
        $this->authorizeResidentManagement();

        return view('residents.create');
    }

    /**
     * Store a newly created resident.
     *
     * Captain and Secretary only.
     */
    public function store(Request $request)
    {
        $this->authorizeResidentManagement();

        $validated = $request->validate(
            [
                'first_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'middle_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'last_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'suffix' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'birth_date' => [
                    'required',
                    'date',
                    'before:today',
                ],

                'sex' => [
                    'required',
                    Rule::in([
                        'Male',
                        'Female',
                    ]),
                ],

                'civil_status' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'contact_number' => [
                    'required',
                    'regex:/^(09|\+639)\d{9}$/',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'address' => [
                    'required',
                    'string',
                    'max:1000',
                ],

                'occupation' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                /*
                |--------------------------------------------------------------------------
                | Residency Start Year
                |--------------------------------------------------------------------------
                |
                | This is stored for the individual resident.
                |
                | Example:
                |     2000 → resident started residing in the barangay in 2000.
                |
                | The years of residency itself is calculated automatically by
                | the Resident model and is NOT stored in the database.
                |
                */

                'residency_start_year' => [
                    'nullable',
                    'integer',
                    'between:1900,' . date('Y'),
                ],

                'status' => [
                    'nullable',
                    Rule::in([
                        'Active',
                        'Archived',
                    ]),
                ],
            ],
            [
                'first_name.required' =>
                    'First name is required.',

                'last_name.required' =>
                    'Last name is required.',

                'birth_date.required' =>
                    'Birth date is required.',

                'birth_date.before' =>
                    'Birth date must be a valid date before today.',

                'contact_number.required' =>
                    'Contact number is required.',

                'contact_number.regex' =>
                    'Please enter a valid Philippine mobile number. Example: 09171234567.',

                'email.email' =>
                    'Please enter a valid email address.',

                'residency_start_year.integer' =>
                    'Residency start year must be a valid year.',

                'residency_start_year.between' =>
                    'Residency start year must be between 1900 and the current year.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Individual Residency History
        |--------------------------------------------------------------------------
        |
        | A resident cannot have a residency start year earlier than their
        | own birth year.
        |
        | Example:
        |
        | Birth year:           2020
        | Residency start year: 2000
        |
        | This is invalid for that individual resident.
        |
        */

        if (
            !empty($validated['residency_start_year']) &&
            !empty($validated['birth_date'])
        ) {
            $birthYear = (int) date(
                'Y',
                strtotime($validated['birth_date'])
            );

            $residencyStartYear = (int) $validated['residency_start_year'];

            if ($residencyStartYear < $birthYear) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'residency_start_year' =>
                            'Residency start year cannot be earlier than the resident\'s birth year.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Resident Check
        |--------------------------------------------------------------------------
        */

        $duplicate = Resident::whereRaw(
            'LOWER(first_name) = ?',
            [
                strtolower(
                    $validated['first_name']
                ),
            ]
        )
            ->whereRaw(
                'LOWER(last_name) = ?',
                [
                    strtolower(
                        $validated['last_name']
                    ),
                ]
            )
            ->whereDate(
                'birth_date',
                $validated['birth_date']
            )
            ->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'duplicate' =>
                        'This resident already exists in the records.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Default Status
        |--------------------------------------------------------------------------
        */

        if (
            !isset($validated['status']) ||
            $validated['status'] === null
        ) {
            $validated['status'] = 'Active';
        }

        /*
        |--------------------------------------------------------------------------
        | Create Resident
        |--------------------------------------------------------------------------
        */

        Resident::create($validated);

        return redirect()
            ->route('residents.index')
            ->with(
                'success',
                'Resident successfully added.'
            );
    }

    /**
     * Display the specified resident.
     *
     * Captain, Secretary, and Treasurer can view.
     */
    public function show(Resident $resident)
    {
        return view(
            'residents.show',
            compact('resident')
        );
    }

    /**
     * Show the form for editing the specified resident.
     *
     * Captain and Secretary only.
     */
    public function edit(Resident $resident)
    {
        $this->authorizeResidentManagement();

        return view(
            'residents.edit',
            compact('resident')
        );
    }

    /**
     * Update the specified resident.
     *
     * Captain and Secretary only.
     */
    public function update(
        Request $request,
        Resident $resident
    ) {
        $this->authorizeResidentManagement();

        $validated = $request->validate(
            [
                'first_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'middle_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'last_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'suffix' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'birth_date' => [
                    'required',
                    'date',
                    'before:today',
                ],

                'sex' => [
                    'required',
                    Rule::in([
                        'Male',
                        'Female',
                    ]),
                ],

                'civil_status' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'contact_number' => [
                    'required',
                    'regex:/^(09|\+639)\d{9}$/',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'address' => [
                    'required',
                    'string',
                    'max:1000',
                ],

                'occupation' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                /*
                |--------------------------------------------------------------------------
                | Residency Start Year
                |--------------------------------------------------------------------------
                |
                | The value belongs to this individual resident.
                |
                */

                'residency_start_year' => [
                    'nullable',
                    'integer',
                    'between:1900,' . date('Y'),
                ],

                'status' => [
                    'nullable',
                    Rule::in([
                        'Active',
                        'Archived',
                    ]),
                ],
            ],
            [
                'contact_number.regex' =>
                    'Please enter a valid Philippine mobile number. Example: 09171234567.',

                'email.email' =>
                    'Please enter a valid email address.',

                'birth_date.before' =>
                    'Birth date must be a valid date before today.',

                'residency_start_year.integer' =>
                    'Residency start year must be a valid year.',

                'residency_start_year.between' =>
                    'Residency start year must be between 1900 and the current year.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Individual Residency History
        |--------------------------------------------------------------------------
        |
        | The resident's residency cannot start before their own birth year.
        |
        */

        if (
            !empty($validated['residency_start_year']) &&
            !empty($validated['birth_date'])
        ) {
            $birthYear = (int) date(
                'Y',
                strtotime($validated['birth_date'])
            );

            $residencyStartYear = (int) $validated['residency_start_year'];

            if ($residencyStartYear < $birthYear) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'residency_start_year' =>
                            'Residency start year cannot be earlier than the resident\'s birth year.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Check During Update
        |--------------------------------------------------------------------------
        */

        $duplicate = Resident::whereRaw(
            'LOWER(first_name) = ?',
            [
                strtolower(
                    $validated['first_name']
                ),
            ]
        )
            ->whereRaw(
                'LOWER(last_name) = ?',
                [
                    strtolower(
                        $validated['last_name']
                    ),
                ]
            )
            ->whereDate(
                'birth_date',
                $validated['birth_date']
            )
            ->where(
                'id',
                '!=',
                $resident->id
            )
            ->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'duplicate' =>
                        'Another resident with the same name and birth date already exists.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Resident
        |--------------------------------------------------------------------------
        */

        $resident->update($validated);

        return redirect()
            ->route('residents.index')
            ->with(
                'success',
                'Resident successfully updated.'
            );
    }

    /**
     * Remove the specified resident.
     *
     * Captain and Secretary only.
     */
    public function destroy(Resident $resident)
    {
        $this->authorizeResidentManagement();

        $resident->delete();

        return redirect()
            ->route('residents.index')
            ->with(
                'success',
                'Resident successfully deleted.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RESIDENT MANAGEMENT AUTHORIZATION
    |--------------------------------------------------------------------------
    |
    | Captain:
    |   Full access
    |
    | Secretary:
    |   Full access
    |
    | Treasurer:
    |   View only
    |
    |--------------------------------------------------------------------------
    */

    private function authorizeResidentManagement(): void
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | User must be authenticated
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
        | User account must be active
        |--------------------------------------------------------------------------
        |
        | We intentionally check the actual status property instead of
        | calling a custom User model method so that Intelephense can
        | properly recognize it.
        |
        */

        if ($user->status !== 'active') {
            abort(
                403,
                'Your account is inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Captain and Secretary can manage residents.
        |--------------------------------------------------------------------------
        */

        if (
            $user->role === 'captain' ||
            $user->role === 'secretary'
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Treasurer and any other role are view-only.
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'You do not have permission to modify resident records.'
        );
    }
}
