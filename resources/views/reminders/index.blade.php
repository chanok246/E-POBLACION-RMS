@extends('layouts.app')

@section('title', 'Schedule & Reminders | e-Poblacion')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         PAGE HEADER
         ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span
                    class="d-inline-flex align-items-center justify-content-center rounded-3"
                    style="
                        width: 42px;
                        height: 42px;
                        background: rgba(111, 66, 193, .12);
                        color: #6f42c1;
                    "
                >
                    <i class="bi bi-calendar3 fs-5"></i>
                </span>

                <div>
                    <h1 class="h3 fw-bold mb-0">
                        Schedule &amp; Reminders
                    </h1>

                    <p class="text-muted mb-0">
                        Manage meetings, hearings, operations, events, and reminders.
                    </p>
                </div>
            </div>
        </div>

        <a
            href="{{ route('reminders.create') }}"
            class="btn btn-primary px-4"
        >
            <i class="bi bi-plus-lg me-2"></i>
            New Reminder
        </a>

    </div>




    {{-- ============================================================
         QUICK SCHEDULE SUMMARY
         ============================================================ --}}
    <div class="row g-3 mb-4">

        {{-- TODAY --}}
        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="text-muted small fw-semibold mb-1">
                                TODAY
                            </div>

                            <div class="fs-2 fw-bold">
                                {{ $todayReminders->count() }}
                            </div>

                            <div class="text-muted small">
                                scheduled today
                            </div>
                        </div>

                        <span
                            class="d-inline-flex align-items-center justify-content-center rounded-3"
                            style="
                                width: 46px;
                                height: 46px;
                                background: rgba(220, 53, 69, .10);
                                color: #dc3545;
                            "
                        >
                            <i class="bi bi-calendar-event fs-5"></i>
                        </span>

                    </div>

                </div>
            </div>

        </div>


        {{-- TOMORROW --}}
        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="text-muted small fw-semibold mb-1">
                                TOMORROW
                            </div>

                            <div class="fs-2 fw-bold">
                                {{ $tomorrowReminders->count() }}
                            </div>

                            <div class="text-muted small">
                                scheduled tomorrow
                            </div>
                        </div>

                        <span
                            class="d-inline-flex align-items-center justify-content-center rounded-3"
                            style="
                                width: 46px;
                                height: 46px;
                                background: rgba(255, 193, 7, .12);
                                color: #b58100;
                            "
                        >
                            <i class="bi bi-calendar2-day fs-5"></i>
                        </span>

                    </div>

                </div>
            </div>

        </div>


        {{-- THIS WEEK --}}
        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>
                            <div class="text-muted small fw-semibold mb-1">
                                THIS WEEK
                            </div>

                            <div class="fs-2 fw-bold">
                                {{ $thisWeekReminders->count() }}
                            </div>

                            <div class="text-muted small">
                                scheduled this week
                            </div>
                        </div>

                        <span
                            class="d-inline-flex align-items-center justify-content-center rounded-3"
                            style="
                                width: 46px;
                                height: 46px;
                                background: rgba(13, 110, 253, .10);
                                color: #0d6efd;
                            "
                        >
                            <i class="bi bi-calendar-week fs-5"></i>
                        </span>

                    </div>

                </div>
            </div>

        </div>

    </div>


    {{-- ============================================================
         SEARCH + FILTERS
         ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-3">

            <form
                method="GET"
                action="{{ route('reminders.index') }}"
            >

                <div class="row g-2 align-items-end">

                    {{-- SEARCH --}}
                    <div class="col-12 col-lg-5">

                        <label
                            for="search"
                            class="form-label small fw-semibold"
                        >
                            Search
                        </label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="text"
                                id="search"
                                name="search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="Search reminders..."
                            >

                        </div>

                    </div>


                    {{-- TYPE --}}
                    <div class="col-12 col-md-4 col-lg-3">

                        <label
                            for="type"
                            class="form-label small fw-semibold"
                        >
                            Type
                        </label>

                        <select
                            id="type"
                            name="type"
                            class="form-select"
                        >
                            <option value="">
                                All Types
                            </option>

                            @foreach($types as $type)
                                <option
                                    value="{{ $type }}"
                                    @selected(request('type') === $type)
                                >
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>

                    </div>


                    {{-- PRIORITY --}}
                    <div class="col-12 col-md-4 col-lg-2">

                        <label
                            for="priority"
                            class="form-label small fw-semibold"
                        >
                            Priority
                        </label>

                        <select
                            id="priority"
                            name="priority"
                            class="form-select"
                        >
                            <option value="">
                                All Priorities
                            </option>

                            @foreach($priorities as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(request('priority') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                    </div>


                    {{-- ACTIONS --}}
                    <div class="col-12 col-md-4 col-lg-2">

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-grow-1"
                            >
                                <i class="bi bi-funnel me-1"></i>
                                Filter
                            </button>

                            @if(request()->hasAny([
                                'search',
                                'type',
                                'priority'
                            ]))
                                <a
                                    href="{{ route('reminders.index') }}"
                                    class="btn btn-outline-secondary"
                                    title="Clear filters"
                                >
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================
         UPCOMING SCHEDULE
         ============================================================ --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>
                    <h2 class="h5 fw-bold mb-1">
                        Upcoming Schedule
                    </h2>

                    <p class="text-muted small mb-0">
                        Your scheduled activities and reminders.
                    </p>
                </div>

                @if($upcomingReminders->count() > 0)
                    <span class="badge text-bg-light border">
                        {{ $upcomingReminders->count() }}
                        upcoming
                    </span>
                @endif

            </div>

        </div>


        <div class="card-body p-4">

            @if($upcomingReminders->isEmpty())

                {{-- EMPTY STATE --}}
                <div class="text-center py-5">

                    <div
                        class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="
                            width: 72px;
                            height: 72px;
                            background: rgba(111, 66, 193, .10);
                            color: #6f42c1;
                        "
                    >
                        <i class="bi bi-calendar-check fs-2"></i>
                    </div>

                    <h3 class="h5 fw-bold">
                        No upcoming reminders
                    </h3>

                    <p class="text-muted mb-4">
                        There are no scheduled activities at the moment.
                    </p>

                    <a
                        href="{{ route('reminders.create') }}"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-lg me-2"></i>
                        Create Your First Reminder
                    </a>

                </div>

            @else

                {{-- =================================================
                     REMINDER LIST
                     ================================================= --}}
                <div class="d-flex flex-column gap-3">

                    @foreach($upcomingReminders as $reminder)

                        @php
                            $priorityClass = match($reminder->priority) {
                                'urgent' => 'danger',
                                'important' => 'warning',
                                default => 'primary',
                            };

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

                            $isToday = $reminder->scheduled_at->isToday();
                            $isTomorrow = $reminder->scheduled_at->isTomorrow();
                        @endphp

                        <div
                            class="border rounded-3 p-3 p-lg-4"
                            style="background: #fff;"
                        >

                            <div class="row g-3 align-items-center">

                                {{-- ICON --}}
                                <div class="col-auto">

                                    <div
                                        class="d-flex align-items-center justify-content-center rounded-3"
                                        style="
                                            width: 52px;
                                            height: 52px;
                                            background: rgba(111, 66, 193, .10);
                                            color: #6f42c1;
                                        "
                                    >
                                        <i class="bi bi-{{ $typeIcon }} fs-4"></i>
                                    </div>

                                </div>


                                {{-- MAIN INFORMATION --}}
                                <div class="col">

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">

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
                                        @endif

                                    </div>


                                    <h3 class="h6 fw-bold mb-1">
                                        {{ $reminder->title }}
                                    </h3>


                                    <div class="small text-muted">

                                        @if($isToday)
                                            <span class="fw-semibold text-danger">
                                                Today
                                            </span>
                                        @elseif($isTomorrow)
                                            <span class="fw-semibold text-warning">
                                                Tomorrow
                                            </span>
                                        @else
                                            {{ $reminder->scheduled_at->format('l, F j, Y') }}
                                        @endif

                                        <span class="mx-1">
                                            •
                                        </span>

                                        {{ $reminder->scheduled_at->format('g:i A') }}

                                    </div>


                                    @if($reminder->location)
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $reminder->location }}
                                        </div>
                                    @endif

                                </div>


                                {{-- ACTIONS --}}
                                <div class="col-12 col-lg-auto">

                                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">

                                        <a
                                            href="{{ route('reminders.show', $reminder) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i class="bi bi-eye me-1"></i>
                                            View
                                        </a>

                                        <a
                                            href="{{ route('reminders.edit', $reminder) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                        >
                                            <i class="bi bi-pencil me-1"></i>
                                            Edit
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            @endif

        </div>


        {{-- =========================================================
             PAGINATION
             ========================================================= --}}
        @if($reminders->hasPages())

            <div class="card-footer bg-white border-0 px-4 pb-4">

                {{ $reminders->links() }}

            </div>

        @endif

    </div>


    {{-- ============================================================
         QUICK WEEK VIEW
         ============================================================ --}}
    @if($todayReminders->count() > 0 || $tomorrowReminders->count() > 0)

        <div class="row g-4 mt-1">

            {{-- TODAY --}}
            <div class="col-12 col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white border-0 pt-4 px-4">

                        <div class="d-flex align-items-center gap-2">

                            <span class="text-danger">
                                <i class="bi bi-circle-fill small"></i>
                            </span>

                            <h2 class="h6 fw-bold mb-0">
                                Today's Schedule
                            </h2>

                        </div>

                    </div>

                    <div class="card-body px-4">

                        @forelse($todayReminders as $reminder)

                            <a
                                href="{{ route('reminders.show', $reminder) }}"
                                class="text-decoration-none text-reset d-block border-bottom py-3"
                            >

                                <div class="d-flex justify-content-between gap-3">

                                    <div>
                                        <div class="fw-semibold">
                                            {{ $reminder->title }}
                                        </div>

                                        <div class="small text-muted">
                                            {{ $reminder->type }}

                                            @if($reminder->location)
                                                • {{ $reminder->location }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-nowrap small fw-semibold">
                                        {{ $reminder->scheduled_at->format('g:i A') }}
                                    </div>

                                </div>

                            </a>

                        @empty

                            <div class="text-muted small py-3">
                                No scheduled activities today.
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>


            {{-- TOMORROW --}}
            <div class="col-12 col-lg-6">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-header bg-white border-0 pt-4 px-4">

                        <div class="d-flex align-items-center gap-2">

                            <span class="text-warning">
                                <i class="bi bi-circle-fill small"></i>
                            </span>

                            <h2 class="h6 fw-bold mb-0">
                                Tomorrow's Schedule
                            </h2>

                        </div>

                    </div>

                    <div class="card-body px-4">

                        @forelse($tomorrowReminders as $reminder)

                            <a
                                href="{{ route('reminders.show', $reminder) }}"
                                class="text-decoration-none text-reset d-block border-bottom py-3"
                            >

                                <div class="d-flex justify-content-between gap-3">

                                    <div>
                                        <div class="fw-semibold">
                                            {{ $reminder->title }}
                                        </div>

                                        <div class="small text-muted">
                                            {{ $reminder->type }}

                                            @if($reminder->location)
                                                • {{ $reminder->location }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-nowrap small fw-semibold">
                                        {{ $reminder->scheduled_at->format('g:i A') }}
                                    </div>

                                </div>

                            </a>

                        @empty

                            <div class="text-muted small py-3">
                                No scheduled activities tomorrow.
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>

@endsection