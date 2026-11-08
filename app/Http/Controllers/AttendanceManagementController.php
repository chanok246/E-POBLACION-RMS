<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDepartureAuthorization;
use App\Models\AttendanceRecord;
use App\Models\Personnel;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceManagementController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {
    }

    /**
     * Attendance overview.
     */
    public function index(): View
    {
        $today = today()->toDateString();

        $personnelCount = Personnel::query()
            ->where('status', 'active')
            ->count();

        $todayRecords = AttendanceRecord::query()
            ->with('personnel')
            ->whereDate('attendance_date', $today)
            ->latest('time_in')
            ->get();

        $summary = [
            'personnel' => $personnelCount,
            'present' => $todayRecords->whereNotNull('time_in')->count(),
            'timed_out' => $todayRecords->whereNotNull('time_out')->count(),
            'still_present' => $todayRecords
                ->filter(fn ($record) => $record->isOpen())
                ->count(),
            'not_recorded' => max(
                0,
                $personnelCount - $todayRecords->count()
            ),
        ];

        return view('attendance.index', compact(
            'summary',
            'todayRecords'
        ));
    }

    /**
     * Browse attendance records.
     */
    public function records(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'personnel_id' => [
                'nullable',
                'integer',
                Rule::exists('personnel', 'id'),
            ],
            'status' => [
                'nullable',
                Rule::in(['present', 'absent', 'late', 'incomplete']),
            ],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $date = $validated['date'] ?? today()->toDateString();

        $query = AttendanceRecord::query()
            ->with('personnel')
            ->whereDate('attendance_date', $date);

        if (! empty($validated['personnel_id'])) {
            $query->where('personnel_id', $validated['personnel_id']);
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->whereHas('personnel', function ($personnelQuery) use ($search) {
                $personnelQuery
                    ->where('personnel_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->orderByDesc('time_in')
            ->paginate(20)
            ->withQueryString();

        $personnel = Personnel::query()
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('attendance.records', compact(
            'records',
            'personnel',
            'date'
        ));
    }

    /**
     * Personnel management.
     */
    public function personnelIndex(): View
    {
        $personnel = Personnel::query()
            ->with('user')
            ->withCount('attendanceRecords')
            ->orderBy('last_name')
            ->paginate(20);

        return view('attendance.personnel.index', compact('personnel'));
    }

    /**
     * Show personnel registration form.
     */
    public function createPersonnel(): View
    {
        $eligibleUsers = User::query()
            ->whereIn('role', [
                User::ROLE_CAPTAIN,
                User::ROLE_SECRETARY,
            ])
            ->where('status', User::STATUS_ACTIVE)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('personnel')
                    ->whereColumn('personnel.user_id', 'users.id');
            })
            ->orderBy('name')
            ->get();

        return view('attendance.personnel.create', compact('eligibleUsers'));
    }

    /**
     * Register personnel and assign an initial attendance PIN.
     */
    public function storePersonnel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'personnel_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                'unique:personnel,personnel_id',
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:30'],
            'position' => ['required', 'string', 'max:100'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('personnel', 'user_id'),
            ],
            'pin' => ['required', 'digits:6'],
        ]);

        $linkedUser = null;

        if (! empty($validated['user_id'])) {
            $linkedUser = User::query()
                ->whereKey($validated['user_id'])
                ->whereIn('role', [
                    User::ROLE_CAPTAIN,
                    User::ROLE_SECRETARY,
                ])
                ->where('status', User::STATUS_ACTIVE)
                ->first();

            if (! $linkedUser) {
                return back()
                    ->withInput($request->except('pin'))
                    ->withErrors([
                        'user_id' => 'Only an active Captain or Secretary account can be linked.',
                    ]);
            }
        }

        $personnel = DB::transaction(function () use ($validated, $linkedUser) {
            $personnel = Personnel::create([
                'user_id' => $linkedUser?->id,
                'personnel_id' => trim($validated['personnel_id']),
                'first_name' => trim($validated['first_name']),
                'middle_name' => filled($validated['middle_name'] ?? null)
                    ? trim($validated['middle_name'])
                    : null,
                'last_name' => trim($validated['last_name']),
                'suffix' => filled($validated['suffix'] ?? null)
                    ? trim($validated['suffix'])
                    : null,
                'position' => trim($validated['position']),
                'pin_hash' => Hash::make($validated['pin']),
                'pin_updated_at' => now(),
                'status' => 'active',
            ]);

            $this->auditLog->log(
                'created',
                'attendance_personnel',
                $personnel,
                null,
                [
                    'personnel_id' => $personnel->personnel_id,
                    'name' => $personnel->full_name,
                    'position' => $personnel->position,
                    'linked_user_id' => $personnel->user_id,
                    'status' => $personnel->status,
                ],
                'Registered personnel in Attendance Monitoring.'
            );

            return $personnel;
        });

        return redirect()
            ->route('attendance.personnel.index')
            ->with('success', "Personnel {$personnel->personnel_id} registered successfully.");
    }

    /**
     * Show personnel profile.
     */
    public function showPersonnel(Personnel $personnel): View
    {
        $personnel->load('user');

        $attendanceRecords = $personnel->attendanceRecords()
            ->latest('attendance_date')
            ->paginate(15);

        return view('attendance.personnel.show', compact(
            'personnel',
            'attendanceRecords'
        ));
    }

    /**
     * Show personnel editing form.
     */
    public function editPersonnel(Personnel $personnel): View
    {
        $eligibleUsers = User::query()
            ->whereIn('role', [
                User::ROLE_CAPTAIN,
                User::ROLE_SECRETARY,
            ])
            ->where('status', User::STATUS_ACTIVE)
            ->whereNotExists(function ($query) use ($personnel) {
                $query->selectRaw('1')
                    ->from('personnel')
                    ->whereColumn('personnel.user_id', 'users.id')
                    ->where('personnel.id', '!=', $personnel->id);
            })
            ->orderBy('name')
            ->get();

        return view('attendance.personnel.edit', compact(
            'personnel',
            'eligibleUsers'
        ));
    }

    /**
     * Update personnel profile.
     *
     * Attendance personnel registration and Field Collection
     * account management are independent processes.
     */
    public function updatePersonnel(
        Request $request,
        Personnel $personnel
    ): RedirectResponse {
        $validated = $request->validate([
            'personnel_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('personnel', 'personnel_id')->ignore($personnel->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:30'],
            'position' => ['required', 'string', 'max:100'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('personnel', 'user_id')->ignore($personnel->id),
            ],
        ]);

        $linkedUser = null;

        if (! empty($validated['user_id'])) {
            $linkedUser = User::query()
                ->whereKey($validated['user_id'])
                ->whereIn('role', [
                    User::ROLE_CAPTAIN,
                    User::ROLE_SECRETARY,
                ])
                ->where('status', User::STATUS_ACTIVE)
                ->first();

            if (! $linkedUser) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'user_id' => 'Only an active Captain or Secretary account can be linked.',
                    ]);
            }
        }

        $oldValues = [
            'personnel_id' => $personnel->personnel_id,
            'name' => $personnel->full_name,
            'position' => $personnel->position,
            'linked_user_id' => $personnel->user_id,
        ];

        DB::transaction(function () use (
            $personnel,
            $validated,
            $linkedUser,
            $oldValues
        ) {
            $personnel->update([
                'user_id' => $linkedUser?->id,
                'personnel_id' => trim($validated['personnel_id']),
                'first_name' => trim($validated['first_name']),
                'middle_name' => filled($validated['middle_name'] ?? null)
                    ? trim($validated['middle_name'])
                    : null,
                'last_name' => trim($validated['last_name']),
                'suffix' => filled($validated['suffix'] ?? null)
                    ? trim($validated['suffix'])
                    : null,
                'position' => trim($validated['position']),
            ]);

            $this->auditLog->log(
                'updated',
                'attendance_personnel',
                $personnel,
                $oldValues,
                [
                    'personnel_id' => $personnel->personnel_id,
                    'name' => $personnel->full_name,
                    'position' => $personnel->position,
                    'linked_user_id' => $personnel->user_id,
                ],
                'Updated personnel profile.'
            );
        });

        return redirect()
            ->route('attendance.personnel.show', $personnel)
            ->with('success', 'Personnel profile updated successfully.');
    }

    /**
     * Deactivate attendance personnel instead of deleting the record.
     *
     * This does not modify a separate Field Personnel account.
     * Any active attendance QR token is revoked.
     */
    public function deactivatePersonnel(Personnel $personnel): RedirectResponse
    {
        if ($personnel->status !== 'active') {
            return back()->with(
                'error',
                'This personnel record is already inactive.'
            );
        }

        DB::transaction(function () use ($personnel) {
            $oldStatus = $personnel->status;
            $hadQrToken = filled($personnel->qr_token_hash);

            $personnel->update([
                'status' => 'inactive',
                'qr_token_hash' => null,
                'qr_revoked_at' => $hadQrToken
                    ? now()
                    : $personnel->qr_revoked_at,
            ]);

            $this->auditLog->log(
                'deactivated',
                'attendance_personnel',
                $personnel,
                ['status' => $oldStatus],
                [
                    'status' => 'inactive',
                    'qr_token_revoked' => $hadQrToken,
                ],
                'Deactivated attendance personnel. Any active attendance QR token was revoked. Independent Field Collection accounts were not changed.'
            );
        });

        return redirect()
            ->route('attendance.personnel.index')
            ->with(
                'success',
                'Personnel deactivated. Attendance history was preserved and any active attendance QR token was revoked. Separate Field Collection accounts were not changed.'
            );
    }

    /**
     * Issue or replace a QR token.
     *
     * Raw token is displayed only once and is never stored.
     */
    public function issueQr(Personnel $personnel): View
    {
        abort_unless($personnel->isActive(), 404);

        $token = Str::random(64);
        $hadPreviousToken = filled($personnel->qr_token_hash);

        DB::transaction(function () use (
            $personnel,
            $token,
            $hadPreviousToken
        ) {
            $personnel->update([
                'qr_token_hash' => hash('sha256', $token),
                'qr_issued_at' => now(),
                'qr_revoked_at' => null,
            ]);

            $this->auditLog->log(
                $hadPreviousToken ? 'qr_replaced' : 'qr_issued',
                'attendance_personnel',
                $personnel,
                null,
                [
                    'personnel_id' => $personnel->personnel_id,
                    'qr_token_issued' => true,
                    'previous_token_revoked' => $hadPreviousToken,
                ],
                $hadPreviousToken
                    ? 'Replaced personnel QR token. Previous token is no longer valid.'
                    : 'Issued personnel QR token.'
            );
        });

        return view('attendance.personnel.qr-card', [
            'personnel' => $personnel->fresh(),
            'qrSvg' => QrCode::format('svg')
                ->size(260)
                ->margin(2)
                ->generate($token),
            'tokenIssuedAt' => now(),
        ]);
    }

    /**
     * Captain-only attendance PIN reset.
     */
    public function resetPin(
        Request $request,
        Personnel $personnel
    ): RedirectResponse {
        abort_unless($request->user()?->isCaptain(), 403);

        $validated = $request->validate([
            'pin' => ['required', 'digits:6', 'confirmed'],
            'pin_confirmation' => ['required', 'digits:6'],
        ]);

        DB::transaction(function () use ($personnel, $validated) {
            $personnel->update([
                'pin_hash' => Hash::make($validated['pin']),
                'pin_updated_at' => now(),
            ]);

            $this->auditLog->log(
                'pin_reset',
                'attendance_personnel',
                $personnel,
                null,
                [
                    'pin_reset' => true,
                    'pin_updated_at' => $personnel->pin_updated_at?->toDateTimeString(),
                ],
                'Captain reset the personnel attendance PIN.'
            );
        });

        return back()->with('success', 'Personnel PIN reset successfully.');
    }

    /**
     * Captain-only emergency departure authorization.
     *
     * The original Time In is preserved. Time Out remains null.
     */
    public function authorizeDeparture(
        Request $request,
        AttendanceRecord $attendanceRecord
    ): RedirectResponse {
        abort_unless($request->user()?->isCaptain(), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'actual_departure_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $attendanceRecord->hasTimedIn()) {
            return back()->withErrors([
                'attendance' => 'An emergency departure authorization requires an existing Time In.',
            ]);
        }

        if ($attendanceRecord->hasTimedOut()) {
            return back()->withErrors([
                'attendance' => 'This attendance record already has a Time Out.',
            ]);
        }

        if ($attendanceRecord->departureAuthorization()->exists()) {
            return back()->withErrors([
                'attendance' => 'An emergency departure authorization already exists for this record.',
            ]);
        }

        DB::transaction(function () use (
            $request,
            $attendanceRecord,
            $validated
        ) {
            $authorization = AttendanceDepartureAuthorization::create([
                'attendance_record_id' => $attendanceRecord->id,
                'actual_departure_at' => $validated['actual_departure_at'] ?? null,
                'reason' => trim($validated['reason']),
                'authorized_by' => $request->user()->id,
                'authorized_at' => now(),
                'remarks' => filled($validated['remarks'] ?? null)
                    ? trim($validated['remarks'])
                    : null,
            ]);

            $this->auditLog->log(
                'emergency_departure_authorized',
                'attendance',
                $attendanceRecord,
                null,
                [
                    'authorization_id' => $authorization->id,
                    'personnel_id' => $attendanceRecord->personnel_id,
                    'attendance_date' => $attendanceRecord->attendance_date?->toDateString(),
                    'actual_departure_at' => $authorization->actual_departure_at?->toDateTimeString(),
                    'reason' => $authorization->reason,
                    'authorized_by' => $request->user()->id,
                    'authorized_at' => $authorization->authorized_at->toDateTimeString(),
                ],
                'Captain authorized an emergency departure. Original attendance times were preserved.'
            );
        });

        return back()->with(
            'success',
            'Emergency departure authorization recorded. The original Time In and missing Time Out were preserved.'
        );
    }

    /**
     * Attendance reports for a selected date range.
     */
    public function reports(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'personnel_id' => [
                'nullable',
                'integer',
                Rule::exists('personnel', 'id'),
            ],
        ]);

        $from = $validated['from']
            ?? today()->startOfMonth()->toDateString();

        $to = $validated['to']
            ?? today()->toDateString();

        $query = AttendanceRecord::query()
            ->with('personnel')
            ->whereBetween('attendance_date', [$from, $to]);

        if (! empty($validated['personnel_id'])) {
            $query->where('personnel_id', $validated['personnel_id']);
        }

        $records = $query
            ->orderBy('attendance_date')
            ->orderBy('time_in')
            ->paginate(50)
            ->withQueryString();

        $summaryQuery = AttendanceRecord::query()
            ->whereBetween('attendance_date', [$from, $to]);

        if (! empty($validated['personnel_id'])) {
            $summaryQuery->where('personnel_id', $validated['personnel_id']);
        }

        $summary = [
            'total_records' => (clone $summaryQuery)->count(),
            'timed_in' => (clone $summaryQuery)
                ->whereNotNull('time_in')
                ->count(),
            'timed_out' => (clone $summaryQuery)
                ->whereNotNull('time_out')
                ->count(),
            'incomplete' => (clone $summaryQuery)
                ->whereNotNull('time_in')
                ->whereNull('time_out')
                ->count(),
        ];

        $personnel = Personnel::query()
            ->orderBy('last_name')
            ->get();

        return view('attendance.reports', compact(
            'records',
            'summary',
            'personnel',
            'from',
            'to'
        ));
    }
}
