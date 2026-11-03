<?php

namespace App\Http\Controllers;

use App\Models\ReportComplaint;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportComplaintController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Community Category Suggestions
    |--------------------------------------------------------------------------
    |
    | These are suggestions only. Category is free text and is NOT
    | restricted to this list.
    |
    */

    private function communityCategories(): array
    {
        return [
            'Street Light',
            'Drainage',
            'Rainwater / Flooding',
            'Garbage / Waste',
            'Road / Infrastructure',
            'Water Supply',
            'Public Facility',
            'Environmental Concern',
            'Stray Animals',
            'Noise / Disturbance',
            'Other Community Concern',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Person Complaint Category Suggestions
    |--------------------------------------------------------------------------
    |
    | Suggestions only. Category is free text.
    |
    */

    private function personCategories(): array
    {
        return [
            'Verbal Abuse / Harassment',
            'Physical Altercation',
            'Threat / Intimidation',
            'Property Dispute',
            'Boundary Dispute',
            'Debt / Financial Dispute',
            'Family / Domestic Dispute',
            'Noise / Disturbance',
            'Personal Conflict',
            'Other Person Complaint',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    private function statuses(): array
    {
        return [
            'Filed',
            'Under Review',
            'Awaiting Captain Approval',
            'Awaiting Captain Availability',
            'Hearing Scheduled',
            'For Further Information',
            'Under Resolution',
            'Resolved',
            'Closed',
            'Disapproved',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Barangay Reference Data
    |--------------------------------------------------------------------------
    */

    private function puroks(): array
    {
        return [
            'Centro',
            'Purok 1',
            'Purok 2',
            'Purok 3',
            'Purok 4',
            'Purok 5',
            'Purok 6',
        ];
    }

    private function barangays(): array
    {
        return [
            'Poblacion',
        ];
    }

    private function municipalities(): array
    {
        return [
            'Bani',
        ];
    }

    private function provinces(): array
    {
        return [
            'Pangasinan',
        ];
    }

    private function regions(): array
    {
        return [
            'Region I - Ilocos Region',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Officials
    |--------------------------------------------------------------------------
    */

    private function officials()
    {
        return User::query()
            ->whereIn('role', [
                'captain',
                'secretary',
                'staff',
            ])
            ->orderBy('name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Active Residents
    |--------------------------------------------------------------------------
    */

    private function residents()
    {
        return Resident::query()
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Build Location
    |--------------------------------------------------------------------------
    */

    private function buildLocation(array $data): string
    {
        $parts = array_filter([
            $data['purok'] ?? null,
            isset($data['barangay']) && $data['barangay']
                ? 'Barangay ' . $data['barangay']
                : null,
            $data['municipality'] ?? null,
            $data['province'] ?? null,
            $data['region'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    /*
    |--------------------------------------------------------------------------
    | Participant Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Clear all manual complainant fields.
     */
    private function clearManualComplainant(array &$data): void
    {
        $data['complainant_name'] = null;
        $data['complainant_address'] = null;
        $data['complainant_contact_number'] = null;
        $data['complainant_barangay'] = null;
        $data['complainant_municipality'] = null;
        $data['complainant_province'] = null;
    }

    /**
     * Clear all manual respondent/subject fields.
     */
    private function clearManualSubject(array &$data): void
    {
        $data['subject_name'] = null;
        $data['subject_address'] = null;
        $data['subject_contact_number'] = null;
        $data['subject_barangay'] = null;
        $data['subject_municipality'] = null;
        $data['subject_province'] = null;
    }

    /**
     * Determine whether a manual participant name was supplied.
     */
    private function hasManualParticipantName(?string $name): bool
    {
        return filled(trim((string) $name));
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Reports Query
        |--------------------------------------------------------------------------
        */

        $query = ReportComplaint::query()
            ->with([
                'complainant',
                'subjectResident',
                'assignedUser',
                'creator',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($q) use ($search) {

                $q->where(
                    'record_code',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'title',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'description',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'location',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'category',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'purok',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'complainant_name',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'subject_name',
                    'like',
                    "%{$search}%"
                )

                /*
                |--------------------------------------------------------------------------
                | Search Poblacion Resident Complainant
                |--------------------------------------------------------------------------
                */

                ->orWhereHas(
                    'complainant',
                    function ($residentQuery) use ($search) {

                        $residentQuery
                            ->where(
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
                )

                /*
                |--------------------------------------------------------------------------
                | Search Poblacion Resident Respondent
                |--------------------------------------------------------------------------
                */

                ->orWhereHas(
                    'subjectResident',
                    function ($residentQuery) use ($search) {

                        $residentQuery
                            ->where(
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
        | Report Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('report_type')) {
            $query->where(
                'report_type',
                $request->input('report_type')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
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
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category')) {
            $query->where(
                'category',
                $request->input('category')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Paginated Reports
        |--------------------------------------------------------------------------
        */

        $reports = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $totalReports = ReportComplaint::count();

        $pendingReports = ReportComplaint::whereIn(
            'status',
            [
                'Filed',
                'Under Review',
                'Awaiting Captain Approval',
                'Awaiting Captain Availability',
            ]
        )->count();

        $activeReports = ReportComplaint::whereIn(
            'status',
            [
                'Hearing Scheduled',
                'For Further Information',
                'Under Resolution',
            ]
        )->count();

        $resolvedReports = ReportComplaint::where(
            'status',
            'Resolved'
        )->count();

        $closedReports = ReportComplaint::where(
            'status',
            'Closed'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Existing Dashboard Counts
        |--------------------------------------------------------------------------
        */

        $dashboardCounts = [
            'total' => $totalReports,
            'pending' => $pendingReports,
            'active' => $activeReports,
            'resolved' => $resolvedReports,
            'closed' => $closedReports,
        ];

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = ReportComplaint::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('reports-complaints.index', [
            'reports' => $reports,

            'dashboardCounts' => $dashboardCounts,

            'totalReports' => $totalReports,
            'pendingReports' => $pendingReports,
            'activeReports' => $activeReports,
            'resolvedReports' => $resolvedReports,
            'closedReports' => $closedReports,

            'categories' => $categories,
            'officials' => $this->officials(),
            'search' => $search,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view('reports-complaints.create', [
            'residents' => $this->residents(),
            'officials' => $this->officials(),
            'communityCategories' => $this->communityCategories(),
            'personCategories' => $this->personCategories(),
            'puroks' => $this->puroks(),
            'barangays' => $this->barangays(),
            'municipalities' => $this->municipalities(),
            'provinces' => $this->provinces(),
            'regions' => $this->regions(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'report_type' => [
                'required',
                'in:community,person',
            ],

            /*
             * Category is free text. The suggestion lists are only
             * hints in the form and are not enforced here.
             */
            'category' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Complainant may be:
             * - an existing Poblacion resident
             * - an outside/non-resident entered manually
             */
            'complainant_id' => [
                'nullable',
                'exists:residents,id',
            ],

            'complainant_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'complainant_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'complainant_contact_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'complainant_barangay' => [
                'nullable',
                'string',
                'max:255',
            ],

            'complainant_municipality' => [
                'nullable',
                'string',
                'max:255',
            ],

            'complainant_province' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * Respondent may also be:
             * - an existing Poblacion resident
             * - an outside/non-resident entered manually
             */
            'subject_resident_id' => [
                'nullable',
                'exists:residents,id',
            ],

            'subject_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'subject_contact_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'subject_barangay' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_municipality' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_province' => [
                'nullable',
                'string',
                'max:255',
            ],

            'relationship_to_complainant' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_reported' => [
                'nullable',
                'date',
            ],

            'time_reported' => [
                'nullable',
                'date_format:H:i',
            ],

            'incident_date' => [
                'required',
                'date',
            ],

            'incident_time' => [
                'nullable',
                'date_format:H:i',
            ],

            /*
             * Hearing Schedule
             *
             * Applicable only to Person Complaints.
             * All fields remain optional.
             */
            'hearing_date' => [
                'nullable',
                'date',
            ],

            'hearing_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'hearing_location' => [
                'nullable',
                'string',
                'max:500',
            ],

            /*
             * Witnesses for Subpoena / Summons
             *
             * Witness names are manually entered by staff.
             * They are not connected to the Resident table.
             *
             * Multiple witnesses may be entered, preferably
             * one name per line.
             */
            'witnesses' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'purok' => [
                'required',
                'in:' . implode(',', $this->puroks()),
            ],

            'barangay' => [
                'required',
                'in:' . implode(',', $this->barangays()),
            ],

            'municipality' => [
                'required',
                'in:' . implode(',', $this->municipalities()),
            ],

            'province' => [
                'required',
                'in:' . implode(',', $this->provinces()),
            ],

            'region' => [
                'required',
                'in:' . implode(',', $this->regions()),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'summons_requested' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize Complainant
        |--------------------------------------------------------------------------
        |
        | If a resident is selected, resident information is the
        | source of truth and manual fields are cleared.
        |
        | If no resident is selected, the manually entered person
        | must have a name.
        |
        */

        if (filled($validated['complainant_id'] ?? null)) {
            $this->clearManualComplainant($validated);
        } elseif (
            !$this->hasManualParticipantName(
                $validated['complainant_name'] ?? null
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'complainant_name' =>
                        'Please select a resident or enter the complainant\'s name.',
                ]);
        } else {
            $validated['complainant_id'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Person Complaint Processing
        |--------------------------------------------------------------------------
        */

        if ($validated['report_type'] === 'person') {
            $hasSubjectResident = filled(
                $validated['subject_resident_id'] ?? null
            );

            $hasManualSubject = $this->hasManualParticipantName(
                $validated['subject_name'] ?? null
            );

            /*
             * Person complaints require a respondent.
             */
            if (!$hasSubjectResident && !$hasManualSubject) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subject_name' =>
                            'Please select a resident or enter the respondent\'s name.',
                    ]);
            }

            /*
             * Prevent the same resident from being both
             * complainant and respondent.
             */
            if (
                $hasSubjectResident
                && filled($validated['complainant_id'] ?? null)
                && (int) $validated['complainant_id']
                    === (int) $validated['subject_resident_id']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subject_resident_id' =>
                            'The complainant and respondent cannot be the same resident.',
                    ]);
            }

            /*
             * Resident respondent becomes the source of truth.
             */
            if ($hasSubjectResident) {
                $this->clearManualSubject($validated);
            } else {
                $validated['subject_resident_id'] = null;
            }

            /*
             * Summons/subpoena request applies only to person complaints.
             */
            $validated['summons_requested'] = (bool) (
                $validated['summons_requested'] ?? false
            );

            /*
             * Hearing Schedule
             *
             * A hearing schedule is optional.
             *
             * If a hearing date is supplied, the new complaint
             * starts with the "Hearing Scheduled" status.
             *
             * If no hearing date is supplied, all hearing fields
             * remain empty.
             */
            if (filled($validated['hearing_date'] ?? null)) {
                $validated['status'] = 'Hearing Scheduled';
            } else {
                $validated['hearing_date'] = null;
                $validated['hearing_time'] = null;
                $validated['hearing_location'] = null;
            }
        } else {
            /*
             * Community reports have:
             * - no respondent
             * - no relationship
             * - no summons/subpoena
             * - no hearing schedule
             * - no witnesses
             */
            $validated['subject_resident_id'] = null;

            $this->clearManualSubject($validated);

            $validated['relationship_to_complainant'] = null;
            $validated['summons_requested'] = false;
            $validated['witnesses'] = null;

            $validated['hearing_date'] = null;
            $validated['hearing_time'] = null;
            $validated['hearing_location'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Defaults
        |--------------------------------------------------------------------------
        */

        $validated['date_reported'] = $validated['date_reported']
            ?? now()->toDateString();

        $validated['time_reported'] = $validated['time_reported']
            ?? now()->format('H:i');

        $validated['location'] = $this->buildLocation(
            $validated
        );

        /*
         * If no hearing schedule was supplied, a newly filed
         * complaint starts as Filed.
         *
         * If a hearing date was supplied above, the status has
         * already been changed to Hearing Scheduled.
         */
        if (!filled($validated['hearing_date'] ?? null)) {
            $validated['status'] = 'Filed';
        }

        $validated['resolved_at'] = null;
        $validated['closed_at'] = null;

        $validated['created_by'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Generate Unique RC-YYYY-#### Record Code
        |--------------------------------------------------------------------------
        */

        $report = DB::transaction(function () use ($validated) {
            $year = now()->format('Y');

            $lastReport = ReportComplaint::query()
                ->where(
                    'record_code',
                    'like',
                    "RC-{$year}-%"
                )
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;

            if ($lastReport) {
                $parts = explode(
                    '-',
                    $lastReport->record_code
                );

                $lastNumber = (int) end($parts);

                $nextNumber = $lastNumber + 1;
            }

            $validated['record_code'] = sprintf(
                'RC-%s-%04d',
                $year,
                $nextNumber
            );

            return ReportComplaint::create($validated);
        });

        return redirect()
            ->route(
                'reports-complaints.show',
                $report
            )
            ->with(
                'success',
                'Report/Complaint filed successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ReportComplaint $reportsComplaint
    ): View {
        $reportsComplaint->load([
            'complainant',
            'subjectResident',
            'assignedUser',
            'creator',
        ]);

        return view(
            'reports-complaints.show',
            compact('reportsComplaint')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        ReportComplaint $reportsComplaint
    ): View|RedirectResponse {
        /*
         * Closed records are view-only.
         */
        if ($reportsComplaint->status === 'Closed') {
            return redirect()
                ->route(
                    'reports-complaints.show',
                    $reportsComplaint
                )
                ->with(
                    'error',
                    'Closed records are view-only and cannot be edited.'
                );
        }

        $reportsComplaint->load([
            'complainant',
            'subjectResident',
            'assignedUser',
            'creator',
        ]);

        return view('reports-complaints.edit', [
            'reportsComplaint' => $reportsComplaint,
            'residents' => $this->residents(),
            'officials' => $this->officials(),
            'communityCategories' => $this->communityCategories(),
            'personCategories' => $this->personCategories(),
            'statuses' => $this->statuses(),
            'puroks' => $this->puroks(),
            'barangays' => $this->barangays(),
            'municipalities' => $this->municipalities(),
            'provinces' => $this->provinces(),
            'regions' => $this->regions(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        ReportComplaint $reportsComplaint
    ): RedirectResponse {
        /*
         * Closed records are permanently view-only.
         */
        if ($reportsComplaint->status === 'Closed') {
            return redirect()
                ->route(
                    'reports-complaints.show',
                    $reportsComplaint
                )
                ->with(
                    'error',
                    'Closed records are view-only and cannot be edited.'
                );
        }

        $validated = $request->validate([
            'report_type' => [
                'required',
                'in:community,person',
            ],

            /*
             * Category is free text. The suggestion lists are only
             * hints in the form and are not enforced here.
             */
            'category' => [
                'required',
                'string',
                'max:255',
            ],

            'complainant_id' => [
                'nullable',
                'exists:residents,id',
            ],

            'complainant_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'complainant_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'complainant_contact_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'complainant_barangay' => [
                'nullable',
                'string',
                'max:255',
            ],

            'complainant_municipality' => [
                'nullable',
                'string',
                'max:255',
            ],

            'complainant_province' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_resident_id' => [
                'nullable',
                'exists:residents,id',
            ],

            'subject_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'subject_contact_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'subject_barangay' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_municipality' => [
                'nullable',
                'string',
                'max:255',
            ],

            'subject_province' => [
                'nullable',
                'string',
                'max:255',
            ],

            'relationship_to_complainant' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_reported' => [
                'nullable',
                'date',
            ],

            'time_reported' => [
                'nullable',
                'date_format:H:i',
            ],

            'incident_date' => [
                'required',
                'date',
            ],

            'incident_time' => [
                'nullable',
                'date_format:H:i',
            ],

            /*
             * Hearing Schedule
             *
             * Applicable only to Person Complaints.
             */
            'hearing_date' => [
                'nullable',
                'date',
            ],

            'hearing_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'hearing_location' => [
                'nullable',
                'string',
                'max:500',
            ],

            /*
             * Witnesses for Subpoena / Summons
             *
             * Witness names are manually entered by staff.
             * They are not connected to the Resident table.
             *
             * Multiple witnesses may be entered, preferably
             * one name per line.
             */
            'witnesses' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'purok' => [
                'required',
                'in:' . implode(',', $this->puroks()),
            ],

            'barangay' => [
                'required',
                'in:' . implode(',', $this->barangays()),
            ],

            'municipality' => [
                'required',
                'in:' . implode(',', $this->municipalities()),
            ],

            'province' => [
                'required',
                'in:' . implode(',', $this->provinces()),
            ],

            'region' => [
                'required',
                'in:' . implode(',', $this->regions()),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'in:' . implode(',', $this->statuses()),
            ],

            'summons_requested' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Closed Cannot Be Manually Selected
        |--------------------------------------------------------------------------
        |
        | Closing is handled by the dedicated Close action and
        | requires the record to already be Resolved.
        |
        */

        if ($validated['status'] === 'Closed') {
            return back()
                ->withInput()
                ->withErrors([
                    'status' =>
                        'A record can only be closed using the Close action after it has been resolved.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolved Records Cannot Be Reopened
        |--------------------------------------------------------------------------
        */

        if (
            $reportsComplaint->status === 'Resolved'
            && $validated['status'] !== 'Resolved'
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'status' =>
                        'A resolved record cannot be reopened or moved back to another status.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Complainant
        |--------------------------------------------------------------------------
        */

        if (filled($validated['complainant_id'] ?? null)) {
            $this->clearManualComplainant($validated);
        } elseif (
            !$this->hasManualParticipantName(
                $validated['complainant_name'] ?? null
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'complainant_name' =>
                        'Please select a resident or enter the complainant\'s name.',
                ]);
        } else {
            $validated['complainant_id'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Person Complaint Processing
        |--------------------------------------------------------------------------
        */

        if ($validated['report_type'] === 'person') {
            $hasSubjectResident = filled(
                $validated['subject_resident_id'] ?? null
            );

            $hasManualSubject = $this->hasManualParticipantName(
                $validated['subject_name'] ?? null
            );

            /*
             * Person complaints require a respondent.
             */
            if (!$hasSubjectResident && !$hasManualSubject) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subject_name' =>
                            'Please select a resident or enter the respondent\'s name.',
                    ]);
            }

            /*
             * Prevent same resident from being both parties.
             */
            if (
                $hasSubjectResident
                && filled($validated['complainant_id'] ?? null)
                && (int) $validated['complainant_id']
                    === (int) $validated['subject_resident_id']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'subject_resident_id' =>
                            'The complainant and respondent cannot be the same resident.',
                    ]);
            }

            /*
             * Resident respondent becomes the source of truth.
             */
            if ($hasSubjectResident) {
                $this->clearManualSubject($validated);
            } else {
                $validated['subject_resident_id'] = null;
            }

            /*
             * Summons/subpoena applies only to person complaints.
             */
            $validated['summons_requested'] = (bool) (
                $validated['summons_requested'] ?? false
            );

            /*
             * Hearing Schedule
             *
             * A hearing can be added or rescheduled while the
             * complaint is still active.
             *
             * Resolved, Closed, and Disapproved records are not
             * moved back to Hearing Scheduled.
             */
            if (filled($validated['hearing_date'] ?? null)) {
                if (
                    !in_array(
                        $reportsComplaint->status,
                        [
                            'Resolved',
                            'Closed',
                            'Disapproved',
                        ],
                        true
                    )
                ) {
                    $validated['status'] = 'Hearing Scheduled';
                }
            } else {
                /*
                 * If no hearing date remains, clear the entire
                 * hearing schedule.
                 */
                $validated['hearing_date'] = null;
                $validated['hearing_time'] = null;
                $validated['hearing_location'] = null;
            }
        } else {
            /*
             * Community reports have no respondent,
             * no relationship, no summons, no hearing,
             * and no witnesses.
             */
            $validated['subject_resident_id'] = null;

            $this->clearManualSubject($validated);

            $validated['relationship_to_complainant'] = null;
            $validated['summons_requested'] = false;
            $validated['witnesses'] = null;

            $validated['hearing_date'] = null;
            $validated['hearing_time'] = null;
            $validated['hearing_location'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Preserve Existing Reported Date/Time
        |--------------------------------------------------------------------------
        */

        $validated['date_reported'] =
            $validated['date_reported']
            ?? $reportsComplaint->date_reported
            ?? now()->toDateString();

        $validated['time_reported'] =
            $validated['time_reported']
            ?? $reportsComplaint->time_reported
            ?? now()->format('H:i');

        $validated['location'] = $this->buildLocation(
            $validated
        );

        /*
        |--------------------------------------------------------------------------
        | Resolution Timestamp Handling
        |--------------------------------------------------------------------------
        */

        if ($validated['status'] === 'Resolved') {
            $validated['resolved_at'] =
                $reportsComplaint->resolved_at
                ?? now();
        } else {
            $validated['resolved_at'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Editing Never Directly Closes a Record
        |--------------------------------------------------------------------------
        */

        $validated['closed_at'] = null;

        /*
        |--------------------------------------------------------------------------
        | Preserve Existing Summons Print Timestamp
        |--------------------------------------------------------------------------
        */

        $validated['summons_printed_at'] =
            $reportsComplaint->summons_printed_at;

        /*
        |--------------------------------------------------------------------------
        | Update Record
        |--------------------------------------------------------------------------
        */

        $reportsComplaint->update($validated);

        return redirect()
            ->route(
                'reports-complaints.show',
                $reportsComplaint
            )
            ->with(
                'success',
                'Report/Complaint updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve
    |--------------------------------------------------------------------------
    */

    public function resolve(
        ReportComplaint $reportsComplaint
    ): RedirectResponse {
        if ($reportsComplaint->status === 'Closed') {
            return back()->with(
                'error',
                'A closed record cannot be resolved again.'
            );
        }

        if ($reportsComplaint->status === 'Resolved') {
            return back()->with(
                'error',
                'This record is already resolved.'
            );
        }

        if ($reportsComplaint->status === 'Disapproved') {
            return back()->with(
                'error',
                'A disapproved record cannot be marked as resolved.'
            );
        }

        $reportsComplaint->update([
            'status' => 'Resolved',
            'resolved_at' => now(),
        ]);

        return redirect()
            ->route(
                'reports-complaints.show',
                $reportsComplaint
            )
            ->with(
                'success',
                'Report/Complaint marked as resolved.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */

    public function close(
        ReportComplaint $reportsComplaint
    ): RedirectResponse {
        if ($reportsComplaint->status === 'Closed') {
            return back()->with(
                'error',
                'This record is already closed.'
            );
        }

        /*
         * Only Resolved records can be closed.
         */
        if ($reportsComplaint->status !== 'Resolved') {
            return back()->with(
                'error',
                'Only resolved records can be closed.'
            );
        }

        $reportsComplaint->update([
            'status' => 'Closed',
            'closed_at' => now(),
        ]);

        return redirect()
            ->route(
                'reports-complaints.show',
                $reportsComplaint
            )
            ->with(
                'success',
                'Report/Complaint closed successfully. The record is now view-only.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Print Subpoena / Summons
    |--------------------------------------------------------------------------
    */

    public function printSubpoena(
        ReportComplaint $reportsComplaint
    ): View|RedirectResponse {
        $reportsComplaint->load([
            'complainant',
            'subjectResident',
            'assignedUser',
            'creator',
        ]);

        /*
         * Subpoena/summons is only applicable to person complaints.
         */
        if (!$reportsComplaint->isPersonComplaint()) {
            return back()->with(
                'error',
                'Subpoena/summons is only applicable to person complaints.'
            );
        }

        /*
         * A subpoena/summons must first be requested.
         */
        if (!$reportsComplaint->hasSummonsRequest()) {
            return back()->with(
                'error',
                'No subpoena/summons request has been recorded for this complaint.'
            );
        }

        /*
         * Record that the document was printed.
         */
        $reportsComplaint->update([
            'summons_printed_at' => now(),
        ]);

        /*
         * The complete ReportComplaint record is passed to the
         * subpoena Blade, including:
         *
         * - hearing_date
         * - hearing_time
         * - hearing_location
         * - witnesses
         *
         * The subpoena template can therefore display the same
         * hearing schedule and manually entered witness names
         * stored in the case record.
         */
        return view(
            'reports-complaints.subpoena',
            compact('reportsComplaint')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ReportComplaint $reportsComplaint
    ): RedirectResponse {
        /*
         * Permanent deletion is intentionally disabled.
         * Records should remain available for recordkeeping and
         * may instead be handled through the archive workflow.
         */
        return back()->with(
            'error',
            'Permanent deletion of Reports & Complaints is disabled. Use the archive workflow instead.'
        );
    }
}