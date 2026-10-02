@extends('layouts.app')

@section('title', 'Reminder Details | e-Poblacion')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         PAGE HEADER
         ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <div class="d-flex align-items-center gap-2">

                <a
                    href="{{ route('reminders.index') }}"
                    class="btn btn-light border rounded-3"
                    title="Back to Schedule & Reminders"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div>
                    <h1 class="h3 fw-bold mb-1">
                        Reminder Details
                    </h1>

                    <p class="text-muted mb-0">
                        View the complete schedule and reminder information.
                    </p>
                </div>

            </div>
        </div>


        {{-- ========================================================
             TOP ACTIONS
             ======================================================== --}}
        <div class="d-flex flex-wrap gap-2">

            @if($reminder->isScheduled())

                <a
                    href="{{ route('reminders.edit', $reminder) }}"
                    class="btn btn-outline-primary"
                >
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>

            @endif

            <a
                href="{{ route('reminders.index') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-calendar3 me-1"></i>
                Schedule
            </a>

        </div>

    </div>


    {{-- ============================================================
         SUCCESS / ERROR MESSAGES
         ============================================================ --}}
    @if(session('success'))

        <div
            class="alert alert-success alert-dismissible fade show border-0 shadow-sm"
            role="alert"
        >
            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>
        </div>

    @endif


    @if(session('error'))

        <div
            class="alert alert-danger alert-dismissible fade show border-0 shadow-sm"
            role="alert"
        >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>
        </div>

    @endif


    <div class="row g-4">

        {{-- ========================================================
             MAIN DETAILS
             ======================================================== --}}
        <div class="col-12 col-xl-8">

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 pt-4 px-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

                        <div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">

                                @php
                                    $typeIcon = match($reminder->type) {
                                        'Meeting' => 'people',
                                        'Hearing' => 'person-lines-fill',
                                        'Operation' => 'truck',
                                        'Event' => 'calendar-event',
                                        'Barangay Activity' => 'building',
                                        'Deadline' => 'hourglass-split',
                                        'Inspection' => 'clipboard-check',
                                        'Follow-up' => 'arrow-repeat',
                                        'General Reminder' => 'bell',
                                        default => 'calendar3',
                                    };
                                @endphp

                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-3"
                                    style="
                                        width: 46px;
                                        height: 46px;
                                        background: rgba(111, 66, 193, .10);
                                        color: #6f42c1;
                                    "
                                >
                                    <i class="bi bi-{{ $typeIcon }} fs-5"></i>
                                </span>

                                <span class="badge text-bg-light border">
                                    {{ $reminder->type }}
                                </span>

                                @if($reminder->priority === 'urgent')

                                    <span class="badge text-bg-danger">
                                        Urgent
                                    </span>

                                @elseif($reminder->priority === 'important')

                                    <span class="badge text-bg-warning">
                                        Important
                                    </span>

                                @else

                                    <span class="badge text-bg-secondary">
                                        Normal
                                    </span>

                                @endif

                            </div>

                            <h2 class="h4 fw-bold mb-1">
                                {{ $reminder->title }}
                            </h2>

                            <div class="text-muted small">
                                Created
                                {{ $reminder->created_at->format('F j, Y \a\t g:i A') }}
                            </div>

                        </div>


                        {{-- STATUS --}}
                        <div>

                            @if($reminder->isScheduled())

                                <span class="badge rounded-pill text-bg-primary px-3 py-2">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    Scheduled
                                </span>

                            @elseif($reminder->isCompleted())

                                <span class="badge rounded-pill text-bg-success px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Completed
                                </span>

                            @elseif($reminder->isCancelled())

                                <span class="badge rounded-pill text-bg-secondary px-3 py-2">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancelled
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                <div class="card-body p-4">

                    {{-- =================================================
                         DATE / TIME
                         ================================================= --}}
                    <div
                        class="rounded-3 p-4 mb-4"
                        style="
                            background: rgba(111, 66, 193, .06);
                            border: 1px solid rgba(111, 66, 193, .12);
                        "
                    >

                        <div class="row g-4">

                            <div class="col-12 col-md-6">

                                <div class="small text-muted fw-semibold mb-1">
                                    DATE
                                </div>

                                <div class="d-flex align-items-center gap-2">

                                    <i
                                        class="bi bi-calendar3"
                                        style="color: #6f42c1;"
                                    ></i>

                                    <span class="fw-bold">
                                        {{ $reminder->scheduled_at->format('l, F j, Y') }}
                                    </span>

                                </div>

                            </div>


                            <div class="col-12 col-md-6">

                                <div class="small text-muted fw-semibold mb-1">
                                    TIME
                                </div>

                                <div class="d-flex align-items-center gap-2">

                                    <i
                                        class="bi bi-clock"
                                        style="color: #6f42c1;"
                                    ></i>

                                    <span class="fw-bold">
                                        {{ $reminder->scheduled_at->format('g:i A') }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         LOCATION
                         ================================================= --}}
                    <div class="mb-4">

                        <div class="small text-muted fw-semibold mb-1">
                            LOCATION
                        </div>

                        <div class="d-flex align-items-start gap-2">

                            <i
                                class="bi bi-geo-alt mt-1"
                                style="color: #6f42c1;"
                            ></i>

                            <div>

                                @if($reminder->location)

                                    <div class="fw-semibold">
                                        {{ $reminder->location }}
                                    </div>

                                @else

                                    <span class="text-muted">
                                        No location specified.
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         NOTES
                         ================================================= --}}
                    <div class="mb-4">

                        <div class="small text-muted fw-semibold mb-1">
                            NOTES / PREPARATION
                        </div>

                        @if($reminder->notes)

                            <div
                                class="border rounded-3 p-3"
                                style="white-space: pre-line;"
                            >
                                {{ $reminder->notes }}
                            </div>

                        @else

                            <div class="text-muted">
                                No notes or preparation instructions.
                            </div>

                        @endif

                    </div>


                    {{-- =================================================
                         PRE-REMINDERS
                         ================================================= --}}
                    <div>

                        <div class="small text-muted fw-semibold mb-2">
                            PRE-REMINDERS
                        </div>

                        @if(!empty($reminder->reminder_offsets))

                            <div class="d-flex flex-wrap gap-2">

                                @foreach($reminder->reminder_offsets as $offset)

                                    @php
                                        $offsetLabels = [
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
                                    @endphp

                                    <span class="badge text-bg-light border px-3 py-2">

                                        <i class="bi bi-bell me-1"></i>

                                        {{ $offsetLabels[$offset] ?? $offset }}

                                    </span>

                                @endforeach

                            </div>

                        @else

                            <span class="text-muted">
                                No pre-reminders selected.
                            </span>

                        @endif

                    </div>

                </div>

            </div>


            {{-- ========================================================
                 COMPLETION INFORMATION
                 ======================================================== --}}
            @if($reminder->isCompleted())

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-0 pt-4 px-4">

                        <h2 class="h6 fw-bold mb-0">
                            Completion Information
                        </h2>

                    </div>

                    <div class="card-body p-4">

                        <div class="row g-4">

                            <div class="col-12 col-md-6">

                                <div class="small text-muted fw-semibold mb-1">
                                    COMPLETED ON
                                </div>

                                <div class="fw-semibold">
                                    {{ optional($reminder->completed_at)->format('F j, Y \a\t g:i A') ?? '—' }}
                                </div>

                            </div>


                            <div class="col-12 col-md-6">

                                <div class="small text-muted fw-semibold mb-1">
                                    COMPLETED BY
                                </div>

                                <div class="fw-semibold">

                                    @if($reminder->completedBy)

                                        {{ $reminder->completedBy->name }}

                                    @else

                                        —

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                 CREATOR INFORMATION
                 ======================================================== --}}
            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-3">

                        <div
                            class="d-flex align-items-center justify-content-center rounded-circle"
                            style="
                                width: 44px;
                                height: 44px;
                                background: rgba(13, 110, 253, .10);
                                color: #0d6efd;
                            "
                        >
                            <i class="bi bi-person"></i>
                        </div>

                        <div>

                            <div class="small text-muted">
                                CREATED BY
                            </div>

                            <div class="fw-semibold">

                                {{ $reminder->creator->name ?? 'System User' }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================
             RIGHT ACTION PANEL
             ======================================================== --}}
        <div class="col-12 col-xl-4">

            @if($reminder->isScheduled())

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white border-0 pt-4 px-4">

                        <h2 class="h6 fw-bold mb-1">
                            Reminder Actions
                        </h2>

                        <p class="text-muted small mb-0">
                            Update the status of this scheduled activity.
                        </p>

                    </div>


                    <div class="card-body p-4">

                        {{-- COMPLETE --}}
                        <form
                            method="POST"
                            action="{{ route('reminders.complete', $reminder) }}"
                            class="mb-3"
                            onsubmit="return confirm('Mark this reminder as completed?');"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-success w-100"
                            >
                                <i class="bi bi-check-circle me-2"></i>
                                Mark as Completed
                            </button>

                        </form>


                        {{-- CANCEL --}}
                        <form
                            method="POST"
                            action="{{ route('reminders.cancel', $reminder) }}"
                            onsubmit="return confirm('Cancel this reminder?');"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-outline-secondary w-100"
                            >
                                <i class="bi bi-x-circle me-2"></i>
                                Cancel Reminder
                            </button>

                        </form>

                    </div>

                </div>

            @elseif($reminder->isCompleted())

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body p-4 text-center">

                        <div
                            class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                            style="
                                width: 64px;
                                height: 64px;
                                background: rgba(25, 135, 84, .10);
                                color: #198754;
                            "
                        >
                            <i class="bi bi-check-circle fs-2"></i>
                        </div>

                        <h2 class="h5 fw-bold">
                            Reminder Completed
                        </h2>

                        <p class="text-muted small mb-0">
                            This reminder has already been marked as completed.
                        </p>

                    </div>

                </div>

            @elseif($reminder->isCancelled())

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body p-4 text-center">

                        <div
                            class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                            style="
                                width: 64px;
                                height: 64px;
                                background: rgba(108, 117, 125, .10);
                                color: #6c757d;
                            "
                        >
                            <i class="bi bi-x-circle fs-2"></i>
                        </div>

                        <h2 class="h5 fw-bold">
                            Reminder Cancelled
                        </h2>

                        <p class="text-muted small mb-0">
                            This reminder is no longer active.
                        </p>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                 DANGER ZONE
                 ======================================================== --}}
            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h2 class="h6 fw-bold mb-1">
                        Record Management
                    </h2>

                    <p class="text-muted small mb-3">
                        Delete this reminder permanently if it is no longer needed.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('reminders.destroy', $reminder) }}"
                        onsubmit="return confirm('Delete this reminder permanently? This action cannot be undone.');"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="btn btn-outline-danger w-100"
                        >
                            <i class="bi bi-trash me-2"></i>
                            Delete Reminder
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection