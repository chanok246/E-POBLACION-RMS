<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReminderController extends Controller
{
    private const AUDIT_MODULE = 'Schedule & Reminders';

    /**
     * Display the Schedule & Reminders page.
     */
    public function index(Request $request): View
    {
        $today = now()->startOfDay();
        $endOfWeek = now()->endOfWeek();

        $query = Reminder::query()
            ->with('creator')
            ->where('status', Reminder::STATUS_SCHEDULED);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->input('type')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Priority Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->input('priority')
            );
        }

        $reminders = $query
            ->orderBy('scheduled_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Dashboard-style Schedule Groups
        |--------------------------------------------------------------------------
        */

        $upcomingBaseQuery = Reminder::query()
            ->where('status', Reminder::STATUS_SCHEDULED)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');

        $todayReminders = (clone $upcomingBaseQuery)
            ->whereBetween('scheduled_at', [
                $today,
                $today->copy()->endOfDay(),
            ])
            ->get();

        $tomorrowReminders = (clone $upcomingBaseQuery)
            ->whereBetween('scheduled_at', [
                now()->addDay()->startOfDay(),
                now()->addDay()->endOfDay(),
            ])
            ->get();

        $thisWeekReminders = (clone $upcomingBaseQuery)
            ->whereBetween('scheduled_at', [
                now()->startOfDay(),
                $endOfWeek,
            ])
            ->get();

        $upcomingReminders = (clone $upcomingBaseQuery)
            ->limit(8)
            ->get();

        return view('reminders.index', [
            'reminders' => $reminders,
            'todayReminders' => $todayReminders,
            'tomorrowReminders' => $tomorrowReminders,
            'thisWeekReminders' => $thisWeekReminders,
            'upcomingReminders' => $upcomingReminders,
            'types' => $this->types(),
            'priorities' => $this->priorities(),
        ]);
    }

    /**
     * Return reminders whose notification time is currently due.
     *
     * This endpoint is used by the global reminder popup in
     * layouts/app.blade.php.
     *
     * The reminder itself is still created and managed only through
     * the Schedule & Reminders page.
     */
    public function due(): JsonResponse
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Notification Window
        |--------------------------------------------------------------------------
        |
        | The global popup checks this endpoint periodically.
        |
        | We use a small window around the notification time so that a
        | reminder is still detected if the browser's polling request
        | happens a few seconds late.
        |
        */

        $windowStart = $now->copy()->subMinutes(2);
        $windowEnd = $now->copy()->addSeconds(10);

        $reminders = Reminder::query()
            ->where('status', Reminder::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where(
                'scheduled_at',
                '>=',
                $windowStart->copy()->subDays(3)
            )
            ->where(
                'scheduled_at',
                '<=',
                $now->copy()->addDays(3)
            )
            ->orderBy('scheduled_at')
            ->get();

        $dueReminders = [];

        foreach ($reminders as $reminder) {
            $scheduledAt = $reminder->scheduled_at;

            if (!$scheduledAt) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Selected Reminder Offsets
            |--------------------------------------------------------------------------
            */

            $offsets = $reminder->reminder_offsets;

            if (!is_array($offsets) || empty($offsets)) {
                $offsets = ['at_time'];
            }

            foreach ($offsets as $offset) {
                $notificationAt = $this->calculateNotificationTime(
                    $scheduledAt,
                    (string) $offset
                );

                if (!$notificationAt) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Is This Notification Due?
                |--------------------------------------------------------------------------
                */

                if (
                    $notificationAt->greaterThanOrEqualTo($windowStart) &&
                    $notificationAt->lessThanOrEqualTo($windowEnd)
                ) {
                    $dueReminders[] = [
                        'id' => $reminder->id,

                        'title' => $reminder->title,

                        'type' => $reminder->type,

                        'notes' => $reminder->notes,

                        'location' => $reminder->location,

                        'priority' => $reminder->priority,

                        'scheduled_at' => $scheduledAt->toIso8601String(),

                        'scheduled_at_label' =>
                            $scheduledAt->format('F j, Y g:i A'),

                        'notification_at' =>
                            $notificationAt->toIso8601String(),

                        'notification_at_label' =>
                            $notificationAt->format('F j, Y g:i A'),

                        'offset' => $offset,

                        /*
                        |--------------------------------------------------------------------------
                        | Unique Frontend Notification Key
                        |--------------------------------------------------------------------------
                        |
                        | The browser will use this value with localStorage
                        | so the same reminder/offset is not repeatedly
                        | displayed in the same browser.
                        |
                        */

                        'notification_key' => sprintf(
                            '%s-%s-%s',
                            $reminder->id,
                            $scheduledAt->timestamp,
                            $offset
                        ),

                        'url' => route(
                            'reminders.show',
                            $reminder
                        ),
                    ];
                }
            }
        }

        return response()->json([
            'reminders' => $dueReminders,
        ]);
    }

    /**
     * Calculate when a reminder notification should appear.
     */
    private function calculateNotificationTime(
        Carbon $scheduledAt,
        string $offset
    ): ?Carbon {
        return match ($offset) {
            '3_days' => $scheduledAt->copy()->subDays(3),

            '2_days' => $scheduledAt->copy()->subDays(2),

            '1_day' => $scheduledAt->copy()->subDay(),

            '12_hours' => $scheduledAt->copy()->subHours(12),

            '6_hours' => $scheduledAt->copy()->subHours(6),

            '2_hours' => $scheduledAt->copy()->subHours(2),

            '1_hour' => $scheduledAt->copy()->subHour(),

            '30_minutes' => $scheduledAt->copy()->subMinutes(30),

            'at_time' => $scheduledAt->copy(),

            default => null,
        };
    }

    /**
     * Show the create reminder form.
     */
    public function create(): View
    {
        return view('reminders.create', [
            'types' => $this->types(),
            'priorities' => $this->priorities(),
            'reminderOptions' => $this->reminderOptions(),
        ]);
    }

    /**
     * Store a new reminder.
     */
    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $this->validateReminder($request);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = Reminder::STATUS_SCHEDULED;

        /*
        |--------------------------------------------------------------------------
        | Normalize Date + Time
        |--------------------------------------------------------------------------
        */

        $validated['scheduled_at'] = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['scheduled_date'] . ' ' . $validated['scheduled_time']
        );

        unset(
            $validated['scheduled_date'],
            $validated['scheduled_time']
        );

        /*
        |--------------------------------------------------------------------------
        | Reminder Timing
        |--------------------------------------------------------------------------
        */

        $validated['reminder_offsets'] =
            $request->input('reminder_offsets', []);

        $reminder = Reminder::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        $auditLog->log(
            action: 'Reminder Created',
            module: self::AUDIT_MODULE,
            record: $reminder,
            oldValues: null,
            newValues: [
                'title' => $reminder->title,
                'type' => $reminder->type,
                'scheduled_at' => $reminder->scheduled_at?->format(
                    'Y-m-d H:i:s'
                ),
                'location' => $reminder->location,
                'priority' => $reminder->priority,
                'status' => $reminder->status,
            ],
            description: sprintf(
                'Created reminder "%s" scheduled for %s.',
                $reminder->title,
                $reminder->scheduled_at?->format(
                    'F j, Y g:i A'
                )
            ),
            request: $request
        );

        return redirect()
            ->route('reminders.index')
            ->with(
                'success',
                'Reminder scheduled successfully.'
            );
    }

    /**
     * Display one reminder.
     */
    public function show(Reminder $reminder): View
    {
        $reminder->load([
            'creator',
            'completedBy',
        ]);

        return view('reminders.show', [
            'reminder' => $reminder,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Reminder $reminder): View
    {
        return view('reminders.edit', [
            'reminder' => $reminder,
            'types' => $this->types(),
            'priorities' => $this->priorities(),
            'reminderOptions' => $this->reminderOptions(),
        ]);
    }

    /**
     * Update reminder.
     */
    public function update(
        Request $request,
        Reminder $reminder,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $this->validateReminder($request);

        $oldValues = [
            'title' => $reminder->title,
            'type' => $reminder->type,
            'scheduled_at' => $reminder->scheduled_at?->format(
                'Y-m-d H:i:s'
            ),
            'location' => $reminder->location,
            'priority' => $reminder->priority,
            'status' => $reminder->status,
        ];

        $validated['scheduled_at'] = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['scheduled_date'] . ' ' . $validated['scheduled_time']
        );

        unset(
            $validated['scheduled_date'],
            $validated['scheduled_time']
        );

        $validated['reminder_offsets'] =
            $request->input('reminder_offsets', []);

        $reminder->update($validated);

        $auditLog->log(
            action: 'Reminder Updated',
            module: self::AUDIT_MODULE,
            record: $reminder,
            oldValues: $oldValues,
            newValues: [
                'title' => $reminder->title,
                'type' => $reminder->type,
                'scheduled_at' => $reminder->scheduled_at?->format(
                    'Y-m-d H:i:s'
                ),
                'location' => $reminder->location,
                'priority' => $reminder->priority,
                'status' => $reminder->status,
            ],
            description: sprintf(
                'Updated reminder "%s".',
                $reminder->title
            ),
            request: $request
        );

        return redirect()
            ->route('reminders.show', $reminder)
            ->with(
                'success',
                'Reminder updated successfully.'
            );
    }

    /**
     * Mark reminder as completed.
     */
    public function complete(
        Request $request,
        Reminder $reminder,
        AuditLogService $auditLog
    ): RedirectResponse {
        if ($reminder->status !== Reminder::STATUS_SCHEDULED) {
            return back()->with(
                'error',
                'Only scheduled reminders can be completed.'
            );
        }

        $reminder->update([
            'status' => Reminder::STATUS_COMPLETED,
            'completed_by' => $request->user()->id,
            'completed_at' => now(),
        ]);

        $auditLog->log(
            action: 'Reminder Completed',
            module: self::AUDIT_MODULE,
            record: $reminder,
            oldValues: [
                'status' => Reminder::STATUS_SCHEDULED,
            ],
            newValues: [
                'status' => Reminder::STATUS_COMPLETED,
                'completed_at' => $reminder->completed_at?->format(
                    'Y-m-d H:i:s'
                ),
            ],
            description: sprintf(
                'Marked reminder "%s" as completed.',
                $reminder->title
            ),
            request: $request
        );

        return redirect()
            ->route('reminders.index')
            ->with(
                'success',
                'Reminder marked as completed.'
            );
    }

    /**
     * Cancel reminder.
     */
    public function cancel(
        Request $request,
        Reminder $reminder,
        AuditLogService $auditLog
    ): RedirectResponse {
        if ($reminder->status !== Reminder::STATUS_SCHEDULED) {
            return back()->with(
                'error',
                'Only scheduled reminders can be cancelled.'
            );
        }

        $reminder->update([
            'status' => Reminder::STATUS_CANCELLED,
        ]);

        $auditLog->log(
            action: 'Reminder Cancelled',
            module: self::AUDIT_MODULE,
            record: $reminder,
            oldValues: [
                'status' => Reminder::STATUS_SCHEDULED,
            ],
            newValues: [
                'status' => Reminder::STATUS_CANCELLED,
            ],
            description: sprintf(
                'Cancelled reminder "%s".',
                $reminder->title
            ),
            request: $request
        );

        return redirect()
            ->route('reminders.index')
            ->with(
                'success',
                'Reminder cancelled.'
            );
    }

    /**
     * Delete reminder.
     *
     * We keep this separate from Cancel so the system can preserve
     * operational history through cancellation.
     */
    public function destroy(
        Request $request,
        Reminder $reminder,
        AuditLogService $auditLog
    ): RedirectResponse {
        $title = $reminder->title;

        $auditLog->log(
            action: 'Reminder Deleted',
            module: self::AUDIT_MODULE,
            record: $reminder,
            oldValues: [
                'title' => $reminder->title,
                'type' => $reminder->type,
                'scheduled_at' => $reminder->scheduled_at?->format(
                    'Y-m-d H:i:s'
                ),
                'status' => $reminder->status,
            ],
            newValues: null,
            description: sprintf(
                'Deleted reminder "%s".',
                $title
            ),
            request: $request
        );

        $reminder->delete();

        return redirect()
            ->route('reminders.index')
            ->with(
                'success',
                'Reminder deleted successfully.'
            );
    }

    /**
     * Validation rules.
     */
    private function validateReminder(Request $request): array
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'in:' . implode(',', $this->types()),
            ],

            'scheduled_date' => [
                'required',
                'date',
            ],

            'scheduled_time' => [
                'required',
                'date_format:H:i',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'priority' => [
                'required',
                'in:' . implode(',', array_keys(
                    $this->priorities()
                )),
            ],

            'reminder_offsets' => [
                'nullable',
                'array',
            ],

            'reminder_offsets.*' => [
                'in:' . implode(',', array_keys(
                    $this->reminderOptions()
                )),
            ],
        ]);
    }

    /**
     * Available reminder types.
     */
    private function types(): array
    {
        return [
            Reminder::TYPE_MEETING,
            Reminder::TYPE_HEARING,
            Reminder::TYPE_OPERATION,
            Reminder::TYPE_EVENT,
            Reminder::TYPE_BARANGAY_ACTIVITY,
            Reminder::TYPE_DEADLINE,
            Reminder::TYPE_INSPECTION,
            Reminder::TYPE_FOLLOW_UP,
            Reminder::TYPE_GENERAL,
            Reminder::TYPE_OTHER,
        ];
    }

    /**
     * Available priorities.
     */
    private function priorities(): array
    {
        return [
            Reminder::PRIORITY_NORMAL => 'Normal',
            Reminder::PRIORITY_IMPORTANT => 'Important',
            Reminder::PRIORITY_URGENT => 'Urgent',
        ];
    }

    /**
     * Available pre-reminder intervals.
     */
    private function reminderOptions(): array
    {
        return [
            '3_days' => '3 days before',
            '2_days' => '2 days before',
            '1_day' => '1 day before',
            '12_hours' => '12 hours before',
            '6_hours' => '6 hours before',
            '2_hours' => '2 hours before',
            '1_hour' => '1 hour before',
            '30_minutes' => '30 minutes before',
            'at_time' => 'At scheduled time',
        ];
    }
}