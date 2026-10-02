@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    {{-- ================================================================
         PAGE HEADER
    ================================================================= --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-cloud-lightning-rain me-2"></i>
                Disaster Events
            </h2>

            <p class="text-muted mb-0">
                Manage disaster incidents, household assessments, and relief operations.
            </p>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('disaster-events.create') }}"
               class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Create Disaster Event
            </a>
        </div>

    </div>


    {{-- ================================================================
         FLASH MESSAGES
    ================================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm"
             role="alert">

            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif


    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm"
             role="alert">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif


    {{-- ================================================================
         STATISTICS
    ================================================================= --}}
    <div class="row g-3 mb-4">

        {{-- Total Events --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-1">
                                Total Events
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ $totalEvents ?? 0 }}
                            </h3>
                        </div>

                        <div class="rounded-circle bg-primary bg-opacity-10
                                    p-3 text-primary">

                            <i class="bi bi-cloud-lightning-rain fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Active Events --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-1">
                                Active Events
                            </p>

                            <h3 class="fw-bold text-success mb-0">
                                {{ $activeEvents ?? 0 }}
                            </h3>
                        </div>

                        <div class="rounded-circle bg-success bg-opacity-10
                                    p-3 text-success">

                            <i class="bi bi-activity fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Closed Events --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-1">
                                Completed
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ $completedEvents ?? 0 }}
                            </h3>
                        </div>

                        <div class="rounded-circle bg-info bg-opacity-10
                                    p-3 text-info">

                            <i class="bi bi-check2-square fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Cancelled Events --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <p class="text-muted small mb-1">
                                Cancelled
                            </p>

                            <h3 class="fw-bold text-danger mb-0">
                                {{ $cancelledEvents ?? 0 }}
                            </h3>
                        </div>

                        <div class="rounded-circle bg-danger bg-opacity-10
                                    p-3 text-danger">

                            <i class="bi bi-x-circle fs-4"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         WORKFLOW INFORMATION
    ================================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex align-items-center mb-3">

                <div class="rounded-circle bg-primary bg-opacity-10
                            p-2 text-primary me-3">

                    <i class="bi bi-diagram-3"></i>

                </div>

                <div>

                    <h6 class="fw-bold mb-0">
                        Disaster Response Workflow
                    </h6>

                    <small class="text-muted">
                        Human-in-the-loop assessment and relief process
                    </small>

                </div>

            </div>


            <div class="row g-2 text-center">

                <div class="col-6 col-md">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-bold small">
                            1. Disaster Event
                        </div>

                        <small class="text-muted">
                            Record incident
                        </small>
                    </div>
                </div>


                <div class="col-6 col-md">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-bold small">
                            2. Assessment
                        </div>

                        <small class="text-muted">
                            Assess households
                        </small>
                    </div>
                </div>


                <div class="col-6 col-md">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-bold small">
                            3. Verification
                        </div>

                        <small class="text-muted">
                            Official review
                        </small>
                    </div>
                </div>


                <div class="col-6 col-md">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-bold small">
                            4. Eligibility
                        </div>

                        <small class="text-muted">
                            Approve beneficiary
                        </small>
                    </div>
                </div>


                <div class="col-6 col-md">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-bold small">
                            5. Relief Voucher
                        </div>

                        <small class="text-muted">
                            Generate QR voucher
                        </small>
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         SEARCH & FILTER
    ================================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <form method="GET"
                  action="{{ route('disaster-events.index') }}">

                <div class="row g-3 align-items-end">

                    {{-- Search --}}
                    <div class="col-12 col-lg-5">

                        <label class="form-label fw-semibold">
                            Search Disaster Events
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Event code, name, type, barangay...">

                        </div>

                    </div>


                    {{-- Status --}}
                    <div class="col-12 col-sm-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="">
                                All Statuses
                            </option>

                            <option value="draft"
                                {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>

                            <option value="active"
                                {{ ($status ?? '') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="closed"
                                {{ ($status ?? '') === 'closed' ? 'selected' : '' }}>
                                Completed
                            </option>

                            <option value="cancelled"
                                {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>
                                Cancelled
                            </option>

                        </select>

                    </div>


                    {{-- Disaster Type --}}
                    <div class="col-12 col-sm-6 col-lg-3">

                        <label class="form-label fw-semibold">
                            Disaster Type
                        </label>

                        <select name="type"
                                class="form-select">

                            <option value="">
                                All Types
                            </option>

                            @foreach($disasterTypes as $disasterType)

                                <option value="{{ $disasterType }}"
                                    {{ ($type ?? '') === $disasterType ? 'selected' : '' }}>

                                    {{ $disasterType }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Filter Button --}}
                    <div class="col-12 col-lg-1 d-flex gap-2">

                        <button type="submit"
                                class="btn btn-primary w-100"
                                title="Apply Filters">

                            <i class="bi bi-funnel"></i>

                        </button>

                    </div>

                </div>


                {{-- Clear Filters --}}
                @if(
                    ($search ?? '') !== '' ||
                    ($status ?? '') !== '' ||
                    ($type ?? '') !== ''
                )

                    <div class="mt-3">

                        <a href="{{ route('disaster-events.index') }}"
                           class="btn btn-sm btn-outline-secondary">

                            <i class="bi bi-x-circle me-1"></i>
                            Clear Filters

                        </a>

                    </div>

                @endif

            </form>

        </div>

    </div>


    {{-- ================================================================
         DISASTER EVENT TABLE
    ================================================================= --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="fw-bold mb-1">
                        Disaster Event Records
                    </h5>

                    <small class="text-muted">
                        {{ $disasterEvents->total() }}
                        event(s) found
                    </small>

                </div>

            </div>

        </div>


        <div class="card-body p-0">

            @if($disasterEvents->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="ps-4">
                                    Event
                                </th>

                                <th>
                                    Type
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Date
                                </th>

                                <th class="text-center">
                                    Assessments
                                </th>

                                <th>
                                    Status
                                </th>

                                <th class="text-end pe-4">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($disasterEvents as $event)

                                <tr>

                                    {{-- Event --}}
                                    <td class="ps-4">

                                        <div class="fw-semibold">
                                            {{ $event->name ?? 'Unnamed Event' }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $event->event_code ?? 'No Event Code' }}
                                        </small>

                                    </td>


                                    {{-- Type --}}
                                    <td>

                                        <span class="badge bg-light text-dark border">
                                            {{ $event->type ?? 'Other Disaster' }}
                                        </span>

                                    </td>


                                    {{-- Location --}}
                                    <td>

                                        <div>
                                            {{ $event->barangay ?? 'Barangay Poblacion' }}
                                        </div>

                                        <small class="text-muted">

                                            {{ $event->municipality ?? 'Bani' }},
                                            {{ $event->province ?? 'Pangasinan' }}

                                        </small>

                                    </td>


                                    {{-- Date --}}
                                    <td>

                                        @if($event->start_date)

                                            <div>
                                                {{ $event->start_date->format('M d, Y') }}
                                            </div>

                                        @else

                                            <div class="text-muted">
                                                No start date
                                            </div>

                                        @endif


                                        @if($event->end_date)

                                            <small class="text-muted">

                                                to
                                                {{ $event->end_date->format('M d, Y') }}

                                            </small>

                                        @endif

                                    </td>


                                    {{-- Assessments --}}
                                    <td class="text-center">

                                        <span class="badge bg-primary rounded-pill">

                                            {{ $event->assessments_count ?? 0 }}

                                        </span>

                                    </td>


                                    {{-- Status --}}
                                    <td>

                                        @if($event->status === 'active')

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        @elseif($event->status === 'closed')

                                            <span class="badge bg-info">
                                                Completed
                                            </span>

                                        @elseif($event->status === 'cancelled')

                                            <span class="badge bg-danger">
                                                Cancelled
                                            </span>

                                        @elseif($event->status === 'draft')

                                            <span class="badge bg-secondary">
                                                Draft
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                {{ $event->status ?? 'Unknown' }}
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Actions --}}
                                    <td class="text-end pe-4">

                                        <div class="btn-group">

                                            {{-- View --}}
                                            <a href="{{ route(
                                                'disaster-events.show',
                                                $event
                                            ) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Disaster Event">

                                                <i class="bi bi-eye"></i>

                                            </a>


                                            {{-- Edit --}}
                                            <a href="{{ route(
                                                'disaster-events.edit',
                                                $event
                                            ) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit Disaster Event">

                                                <i class="bi bi-pencil"></i>

                                            </a>


                                            {{-- Cancel / Retain Historical Record --}}
                                            @if($event->status !== 'cancelled')

                                                <form method="POST"
                                                      action="{{ route(
                                                          'disaster-events.destroy',
                                                          $event
                                                      ) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm(
                                                          'Are you sure you want to cancel this disaster event? The historical record will be retained.'
                                                      );">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Cancel Event">

                                                        <i class="bi bi-x-circle"></i>

                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                {{-- Empty State --}}
                <div class="text-center py-5">

                    <div class="mb-3">

                        <i class="bi bi-cloud-slash display-4 text-muted"></i>

                    </div>

                    <h5 class="fw-bold">
                        No Disaster Events Found
                    </h5>

                    <p class="text-muted mb-4">

                        No disaster event records match your current
                        search or filters.

                    </p>

                    <a href="{{ route('disaster-events.create') }}"
                       class="btn btn-primary">

                        <i class="bi bi-plus-circle me-1"></i>
                        Create First Disaster Event

                    </a>

                </div>

            @endif

        </div>


        {{-- Pagination --}}
        @if($disasterEvents->hasPages())

            <div class="card-footer bg-white border-0 py-3">

                {{ $disasterEvents->withQueryString()->links() }}

            </div>

        @endif

    </div>

</div>

@endsection
