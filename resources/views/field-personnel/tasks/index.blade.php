
@extends('field-personnel.layouts.app')

@section('title', 'Assigned Tasks | e-Poblacion')

@section('page-heading')
    Assigned Tasks
@endsection

@section('content')
    @php
        $taskRecords = $tasks ?? collect();

        $statusStyles = [
            'Pending' => 'background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;',
            'Assigned' => 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;',
            'In Progress' => 'background:#fefce8;color:#a16207;border:1px solid #fde68a;',
            'Completed' => 'background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;',
            'Cancelled' => 'background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;',
        ];

        $getStatusStyle = function ($status) use ($statusStyles) {
            return $statusStyles[$status]
                ?? 'background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;';
        };
    @endphp

    <style>
        .tasks-page {
            width: 100%;
        }

        .tasks-intro {
            margin-bottom: 20px;
        }

        .tasks-intro h2 {
            margin: 0 0 6px;
            color: #173b2a;
            font-size: 21px;
            font-weight: 800;
        }

        .tasks-intro p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
        }

        .task-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            padding: 15px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }

        .summary-label {
            display: block;
            margin-bottom: 7px;
            color: #64748b;
            font-size: 12px;
        }

        .summary-value {
            display: block;
            color: #173b2a;
            font-size: 25px;
            font-weight: 800;
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .task-card {
            padding: 16px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
        }

        .task-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
        }

        .task-title {
            margin: 0;
            color: #173b2a;
            font-size: 15px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .task-reference {
            margin-top: 4px;
            color: #94a3b8;
            font-size: 11px;
        }

        .task-status {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            padding: 6px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
        }

        .task-description {
            margin-bottom: 14px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .task-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
            padding-top: 13px;
            border-top: 1px solid #f1f5f9;
        }

        .detail-label {
            display: block;
            margin-bottom: 4px;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .detail-value {
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .task-note {
            margin-top: 13px;
            padding: 11px 12px;
            background: #f8fafc;
            border-radius: 10px;
        }

        .task-note .detail-value {
            display: block;
            margin-top: 4px;
            color: #475569;
            font-weight: 400;
            line-height: 1.5;
        }

        .empty-state {
            padding: 35px 20px;
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 15px;
            text-align: center;
        }

        .empty-icon {
            display: flex;
            width: 55px;
            height: 55px;
            align-items: center;
            justify-content: center;
            margin: 0 auto 13px;
            background: #f0fdf4;
            border-radius: 50%;
            color: #15803d;
            font-size: 25px;
        }

        .empty-state h3 {
            margin: 0 0 7px;
            color: #173b2a;
            font-size: 16px;
            font-weight: 800;
        }

        .empty-state p {
            max-width: 300px;
            margin: 0 auto;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .tasks-footer {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 17px;
            color: #166534;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .back-link:hover {
            background: #dcfce7;
        }

        @media (max-width: 380px) {
            .task-card-header {
                flex-direction: column;
            }

            .task-details {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="tasks-page">

        <div class="tasks-intro">
            <h2>My Assigned Tasks</h2>
            <p>
                View the field activities and household data collection
                assignments given to your account.
            </p>
        </div>

        @php
            $totalTasks = $taskRecords->count();

            $pendingTasks = $taskRecords->filter(function ($task) {
                return in_array(
                    strtolower($task->status ?? 'Pending'),
                    ['pending', 'assigned']
                );
            })->count();

            $completedTasks = $taskRecords->filter(function ($task) {
                return strtolower($task->status ?? '') === 'completed';
            })->count();
        @endphp

        <div class="task-summary">
            <div class="summary-card">
                <span class="summary-label">Total Assigned</span>
                <span class="summary-value">{{ $totalTasks }}</span>
            </div>

            <div class="summary-card">
                <span class="summary-label">Pending Tasks</span>
                <span class="summary-value">{{ $pendingTasks }}</span>
            </div>
        </div>

        @if ($taskRecords->isNotEmpty())

            <div class="task-list">

                @foreach ($taskRecords as $task)
                    @php
                        $status = $task->status ?? 'Pending';

                        $reference = $task->reference_number
                            ?? $task->id
                            ?? 'Not assigned';

                        $title = $task->title
                            ?? $task->task_name
                            ?? 'Field Assignment';

                        $description = $task->description
                            ?? $task->instructions
                            ?? null;

                        $location = $task->location
                            ?? $task->address
                            ?? 'Not specified';

                        $purok = $task->purok
                            ?? 'Not specified';

                        $schedule = $task->scheduled_at
                            ?? $task->schedule
                            ?? $task->due_date
                            ?? null;

                        $assignedAt = $task->assigned_at
                            ?? $task->created_at
                            ?? null;

                        $notes = $task->notes
                            ?? $task->remarks
                            ?? null;
                    @endphp

                    <article class="task-card">

                        <div class="task-card-header">
                            <div>
                                <h3 class="task-title">
                                    {{ $title }}
                                </h3>

                                <div class="task-reference">
                                    Task Reference: {{ $reference }}
                                </div>
                            </div>

                            <span
                                class="task-status"
                                style="{{ $getStatusStyle($status) }}"
                            >
                                {{ $status }}
                            </span>
                        </div>

                        @if ($description)
                            <div class="task-description">
                                {{ $description }}
                            </div>
                        @endif

                        <div class="task-details">

                            <div>
                                <span class="detail-label">Location</span>
                                <span class="detail-value">
                                    {{ $location }}
                                </span>
                            </div>

                            <div>
                                <span class="detail-label">Purok</span>
                                <span class="detail-value">
                                    {{ $purok }}
                                </span>
                            </div>

                            <div>
                                <span class="detail-label">Schedule / Due Date</span>
                                <span class="detail-value">
                                    @if ($schedule)
                                        {{ \Illuminate\Support\Carbon::parse($schedule)->format('M d, Y h:i A') }}
                                    @else
                                        Not specified
                                    @endif
                                </span>
                            </div>

                            <div>
                                <span class="detail-label">Date Assigned</span>
                                <span class="detail-value">
                                    @if ($assignedAt)
                                        {{ \Illuminate\Support\Carbon::parse($assignedAt)->format('M d, Y') }}
                                    @else
                                        Not available
                                    @endif
                                </span>
                            </div>

                        </div>

                        @if ($notes)
                            <div class="task-note">
                                <span class="detail-label">Additional Instructions</span>
                                <span class="detail-value">
                                    {{ $notes }}
                                </span>
                            </div>
                        @endif

                    </article>
                @endforeach

            </div>

            @if (is_callable([$taskRecords, 'links']))
                <div class="tasks-footer">
                    {{ $taskRecords->links() }}
                </div>
            @endif

        @else

            <div class="empty-state">
                <div class="empty-icon">✓</div>

                <h3>No Assigned Tasks</h3>

                <p>
                    There are currently no field assignments associated
                    with your account. New assignments will appear here
                    when they are provided.
                </p>
            </div>

        @endif

        <div class="tasks-footer">
            <a href="{{ url('/field-personnel') }}" class="back-link">
                <span aria-hidden="true">←</span>
                Back to Dashboard
            </a>
        </div>

    </div>
@endsection
