@extends('layouts.app')

@section('title', 'Edit Reminder | e-Poblacion')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
         PAGE HEADER
         ============================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <div class="d-flex align-items-center gap-2">

                <a
                    href="{{ route('reminders.show', $reminder) }}"
                    class="btn btn-light border rounded-3"
                    title="Back to Reminder"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div>
                    <h1 class="h3 fw-bold mb-1">
                        Edit Reminder
                    </h1>

                    <p class="text-muted mb-0">
                        Update the schedule, priority, and reminder settings.
                    </p>
                </div>

            </div>
        </div>

    </div>


    {{-- ============================================================
         VALIDATION ERRORS
         ============================================================ --}}
    @if($errors->any())

        <div class="alert alert-danger border-0 shadow-sm">

            <div class="d-flex gap-2">

                <i class="bi bi-exclamation-triangle-fill mt-1"></i>

                <div>

                    <div class="fw-bold mb-1">
                        Please check the following:
                    </div>

                    <ul class="mb-0 ps-3">

                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- ============================================================
         CURRENT STATUS
         ============================================================ --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body p-3">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

                <div class="d-flex align-items-center gap-3">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3"
                        style="
                            width: 42px;
                            height: 42px;
                            background: rgba(111, 66, 193, .10);
                            color: #6f42c1;
                        "
                    >
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div>

                        <div class="small text-muted">
                            CURRENT REMINDER
                        </div>

                        <div class="fw-bold">
                            {{ $reminder->title }}
                        </div>

                    </div>

                </div>


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

    </div>


    @if(!$reminder->isScheduled())

        {{-- ========================================================
             NON-SCHEDULED NOTICE
             ======================================================== --}}
        <div class="card border-0 shadow-sm">

            <div class="card-body p-5 text-center">

                <div
                    class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                    style="
                        width: 72px;
                        height: 72px;
                        background: rgba(108, 117, 125, .10);
                        color: #6c757d;
                    "
                >
                    @if($reminder->isCompleted())
                        <i class="bi bi-check-circle fs-2"></i>
                    @else
                        <i class="bi bi-x-circle fs-2"></i>
                    @endif
                </div>

                <h2 class="h5 fw-bold">
                    This reminder cannot be edited
                </h2>

                <p class="text-muted mb-4">
                    Completed or cancelled reminders are kept as historical records.
                </p>

                <a
                    href="{{ route('reminders.show', $reminder) }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Reminder
                </a>

            </div>

        </div>

    @else

        {{-- ========================================================
             EDIT FORM
             ======================================================== --}}
        <form
            method="POST"
            action="{{ route('reminders.update', $reminder) }}"
            id="reminderForm"
        >

            @csrf
            @method('PUT')

            <div class="row g-4">

                {{-- =================================================
                     MAIN INFORMATION
                     ================================================= --}}
                <div class="col-12 col-xl-8">

                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <div class="d-flex align-items-center gap-2">

                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-3"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        background: rgba(111, 66, 193, .10);
                                        color: #6f42c1;
                                    "
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </span>

                                <div>

                                    <h2 class="h5 fw-bold mb-0">
                                        Schedule Information
                                    </h2>

                                    <p class="text-muted small mb-0">
                                        Update the details of this activity.
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="card-body p-4">

                            {{-- TITLE --}}
                            <div class="mb-4">

                                <label
                                    for="title"
                                    class="form-label fw-semibold"
                                >
                                    Title
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    class="form-control form-control-lg @error('title') is-invalid @enderror"
                                    value="{{ old('title', $reminder->title) }}"
                                    maxlength="255"
                                    required
                                >

                                @error('title')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- TYPE --}}
                            <div class="mb-4">

                                <label
                                    for="type"
                                    class="form-label fw-semibold"
                                >
                                    Type
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    id="type"
                                    name="type"
                                    class="form-select @error('type') is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        Select schedule type
                                    </option>

                                    @foreach($types as $type)

                                        <option
                                            value="{{ $type }}"
                                            @selected(old('type', $reminder->type) === $type)
                                        >
                                            {{ $type }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('type')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- DATE + TIME --}}
                            <div class="row g-3 mb-4">

                                <div class="col-12 col-md-6">

                                    <label
                                        for="scheduled_date"
                                        class="form-label fw-semibold"
                                    >
                                        Date
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        id="scheduled_date"
                                        name="scheduled_date"
                                        class="form-control @error('scheduled_date') is-invalid @enderror"
                                        value="{{ old('scheduled_date', $reminder->scheduled_at->format('Y-m-d')) }}"
                                        required
                                    >

                                    @error('scheduled_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="col-12 col-md-6">

                                    <label
                                        for="scheduled_time"
                                        class="form-label fw-semibold"
                                    >
                                        Time
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="time"
                                        id="scheduled_time"
                                        name="scheduled_time"
                                        class="form-control @error('scheduled_time') is-invalid @enderror"
                                        value="{{ old('scheduled_time', $reminder->scheduled_at->format('H:i')) }}"
                                        required
                                    >

                                    @error('scheduled_time')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>


                            {{-- LOCATION --}}
                            <div class="mb-4">

                                <label
                                    for="location"
                                    class="form-label fw-semibold"
                                >
                                    Location
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-geo-alt"></i>
                                    </span>

                                    <input
                                        type="text"
                                        id="location"
                                        name="location"
                                        class="form-control @error('location') is-invalid @enderror"
                                        value="{{ old('location', $reminder->location) }}"
                                        maxlength="255"
                                    >

                                </div>

                                @error('location')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            {{-- NOTES --}}
                            <div>

                                <label
                                    for="notes"
                                    class="form-label fw-semibold"
                                >
                                    Notes / Preparation Instructions
                                </label>

                                <textarea
                                    id="notes"
                                    name="notes"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    rows="5"
                                    maxlength="5000"
                                >{{ old('notes', $reminder->notes) }}</textarea>

                                @error('notes')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         REMINDER SCHEDULE
                         ================================================= --}}
                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <div class="d-flex align-items-center gap-2">

                                <span
                                    class="d-inline-flex align-items-center justify-content-center rounded-3"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        background: rgba(13, 110, 253, .10);
                                        color: #0d6efd;
                                    "
                                >
                                    <i class="bi bi-bell"></i>
                                </span>

                                <div>

                                    <h2 class="h5 fw-bold mb-0">
                                        Reminder Schedule
                                    </h2>

                                    <p class="text-muted small mb-0">
                                        Update when the system should remind you.
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="card-body p-4">

                            <div class="alert alert-light border mb-4">

                                <div class="d-flex gap-2">

                                    <i class="bi bi-info-circle text-primary mt-1"></i>

                                    <div class="small">
                                        You can select multiple reminder times.
                                        Changes will be recorded in the audit trail.
                                    </div>

                                </div>

                            </div>


                            @php
                                $selectedOffsets = old(
                                    'reminder_offsets',
                                    $reminder->reminder_offsets ?? []
                                );
                            @endphp


                            <div class="row g-3">

                                @foreach($reminderOptions as $value => $label)

                                    <div class="col-12 col-md-6">

                                        <div class="border rounded-3 p-3 h-100 reminder-option">

                                            <div class="form-check">

                                                <input
                                                    class="form-check-input reminder-checkbox"
                                                    type="checkbox"
                                                    name="reminder_offsets[]"
                                                    value="{{ $value }}"
                                                    id="reminder_{{ $value }}"
                                                    @checked(in_array($value, $selectedOffsets, true))
                                                >

                                                <label
                                                    class="form-check-label fw-semibold"
                                                    for="reminder_{{ $value }}"
                                                >
                                                    {{ $label }}
                                                </label>

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>


                            @error('reminder_offsets')
                                <div class="text-danger small mt-3">
                                    {{ $message }}
                                </div>
                            @enderror

                            @error('reminder_offsets.*')
                                <div class="text-danger small mt-3">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     RIGHT SIDEBAR
                     ================================================= --}}
                <div class="col-12 col-xl-4">

                    {{-- PRIORITY --}}
                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <h2 class="h6 fw-bold mb-1">
                                Priority
                            </h2>

                            <p class="text-muted small mb-0">
                                Update the importance of this reminder.
                            </p>

                        </div>


                        <div class="card-body p-4">

                            @foreach($priorities as $value => $label)

                                @php
                                    $priorityIcon = match($value) {
                                        'urgent' => 'exclamation-triangle',
                                        'important' => 'exclamation-circle',
                                        default => 'dash-circle',
                                    };

                                    $priorityColor = match($value) {
                                        'urgent' => '#dc3545',
                                        'important' => '#b58100',
                                        default => '#6c757d',
                                    };
                                @endphp

                                <div class="border rounded-3 p-3 mb-2 priority-option">

                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="priority"
                                            value="{{ $value }}"
                                            id="priority_{{ $value }}"
                                            @checked(old('priority', $reminder->priority) === $value)
                                        >

                                        <label
                                            class="form-check-label w-100"
                                            for="priority_{{ $value }}"
                                        >

                                            <span class="d-flex align-items-center gap-2">

                                                <i
                                                    class="bi bi-{{ $priorityIcon }}"
                                                    style="color: {{ $priorityColor }};"
                                                ></i>

                                                <span>

                                                    <strong>
                                                        {{ $label }}
                                                    </strong>

                                                    @if($value === 'urgent')

                                                        <small class="d-block text-muted">
                                                            Requires immediate attention.
                                                        </small>

                                                    @elseif($value === 'important')

                                                        <small class="d-block text-muted">
                                                            Should be prepared for in advance.
                                                        </small>

                                                    @else

                                                        <small class="d-block text-muted">
                                                            Normal scheduled activity.
                                                        </small>

                                                    @endif

                                                </span>

                                            </span>

                                        </label>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>


                    {{-- CURRENT SCHEDULE --}}
                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-header bg-white border-0 pt-4 px-4">

                            <h2 class="h6 fw-bold mb-1">
                                Current Schedule
                            </h2>

                            <p class="text-muted small mb-0">
                                Quick reference while editing.
                            </p>

                        </div>


                        <div class="card-body p-4">

                            <div class="mb-3">

                                <div class="small text-muted mb-1">
                                    CURRENT DATE
                                </div>

                                <div class="fw-semibold">
                                    {{ $reminder->scheduled_at->format('F j, Y') }}
                                </div>

                            </div>


                            <div class="mb-3">

                                <div class="small text-muted mb-1">
                                    CURRENT TIME
                                </div>

                                <div class="fw-semibold">
                                    {{ $reminder->scheduled_at->format('g:i A') }}
                                </div>

                            </div>


                            <div>

                                <div class="small text-muted mb-1">
                                    CURRENT STATUS
                                </div>

                                <span class="badge text-bg-primary">
                                    Scheduled
                                </span>

                            </div>

                        </div>

                    </div>


                    {{-- ACTIONS --}}
                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-4">

                            <div class="d-grid gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg"
                                >
                                    <i class="bi bi-check-lg me-2"></i>
                                    Save Changes
                                </button>

                                <a
                                    href="{{ route('reminders.show', $reminder) }}"
                                    class="btn btn-outline-secondary"
                                >
                                    Cancel
                                </a>

                            </div>

                            <div class="text-muted small text-center mt-3">
                                Updating this reminder will be recorded in the audit trail.
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    @endif

</div>


{{-- ================================================================
     PAGE STYLES
     ================================================================ --}}
<style>

    .reminder-option,
    .priority-option {
        transition:
            border-color .15s ease,
            background-color .15s ease,
            box-shadow .15s ease;
    }

    .reminder-option:hover,
    .priority-option:hover {
        border-color: rgba(111, 66, 193, .45) !important;
        background: rgba(111, 66, 193, .025);
    }

    .reminder-option:has(.form-check-input:checked),
    .priority-option:has(.form-check-input:checked) {
        border-color: rgba(111, 66, 193, .55) !important;
        background: rgba(111, 66, 193, .06);
    }

    .form-check-input:checked {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: rgba(111, 66, 193, .55);
        box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .10);
    }

</style>

@endsection