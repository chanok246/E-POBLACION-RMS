@extends('layouts.app')

@section('content')
@php
    $payload = $submission->payload ?? [];

    $household = $payload['household'] ?? [];
    $head = $payload['head'] ?? [];
    $members = $payload['members'] ?? [];

    $status = strtolower($submission->status ?? 'pending');

    $statusClass = match ($status) {
        'approved' => 'success',
        'rejected' => 'danger',
        default => 'warning',
    };

    $statusLabel = match ($status) {
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default => 'Pending for Verification',
    };

    $formatName = function ($person) {
        return trim(implode(' ', array_filter([
            $person['first_name'] ?? '',
            $person['middle_name'] ?? '',
            $person['last_name'] ?? '',
            $person['suffix'] ?? '',
        ])));
    };

    $formatDate = function ($date) {
        if (empty($date)) {
            return '—';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('M d, Y');
        } catch (\Throwable $e) {
            return $date;
        }
    };
@endphp

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="{{ route('admin.field-personnel-submissions.index') }}"
               class="text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Submissions
            </a>

            <h4 class="fw-bold mt-2 mb-1">Review Household Submission</h4>
            <p class="text-muted mb-0">
                Reference: {{ $submission->submission_reference }}
            </p>
        </div>

        <span class="badge bg-{{ $statusClass }} fs-6">
            {{ $statusLabel }}
        </span>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Unable to complete the requested action.</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($status === 'pending')
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            This information is still a pending submission. It will not become
            an official household or resident record unless an authorized
            Captain or Secretary approves it.
        </div>
    @endif

    {{-- Submission Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Submission Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="text-muted small">Submitted By</div>
                    <div class="fw-semibold">
                        {{ $submission->submitted_by_name }}
                    </div>
                    <div class="text-muted small">
                        {{ $submission->submitted_by_position ?: 'Field Personnel' }}
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="text-muted small">Date Submitted</div>
                    <div class="fw-semibold">
                        {{ $submission->submitted_at
                            ? $submission->submitted_at->format('M d, Y h:i A')
                            : '—' }}
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="text-muted small">Reviewed By</div>
                    <div class="fw-semibold">
                        {{ $submission->reviewer?->name ?? 'Not yet reviewed' }}
                    </div>
                    @if ($submission->reviewed_at)
                        <div class="text-muted small">
                            {{ $submission->reviewed_at->format('M d, Y h:i A') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Household Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Household Information</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="text-muted small">Household Address</div>
                    <div class="fw-semibold">
                        {{ $household['address'] ?? '—' }}
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="text-muted small">Purok</div>
                    <div class="fw-semibold">
                        {{ $household['purok'] ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Household Head --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Household Head</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-semibold">{{ $formatName($head) ?: '—' }}</div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-muted small">Birth Date</div>
                    <div class="fw-semibold">
                        {{ $formatDate($head['birth_date'] ?? null) }}
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-muted small">Sex</div>
                    <div class="fw-semibold">{{ $head['sex'] ?? '—' }}</div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-muted small">Civil Status</div>
                    <div class="fw-semibold">{{ $head['civil_status'] ?? '—' }}</div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-muted small">Contact Number</div>
                    <div class="fw-semibold">{{ $head['contact_number'] ?? '—' }}</div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold">{{ $head['email'] ?? '—' }}</div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-muted small">Residency</div>
                    <div class="fw-semibold">{{ $head['residency'] ?? '—' }}</div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="text-muted small">Residency Start Year</div>
                    <div class="fw-semibold">
                        {{ $head['residency_start_year'] ?? '—' }}
                    </div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Occupation</div>
                    <div class="fw-semibold">{{ $head['occupation'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Other Household Members --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                Other Household Members
                <span class="badge bg-secondary ms-1">{{ count($members) }}</span>
            </h5>
        </div>

        <div class="card-body">
            @forelse ($members as $index => $member)
                <div class="{{ $index > 0 ? 'border-top pt-3 mt-3' : '' }}">
                    <div class="fw-bold mb-3">
                        Member {{ $index + 1 }}:
                        {{ $formatName($member) ?: 'Unnamed Member' }}
                    </div>

                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Relationship</div>
                            <div class="fw-semibold">
                                {{ $member['relationship'] ?? '—' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Birth Date</div>
                            <div class="fw-semibold">
                                {{ $formatDate($member['birth_date'] ?? null) }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Sex</div>
                            <div class="fw-semibold">
                                {{ $member['sex'] ?? '—' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Civil Status</div>
                            <div class="fw-semibold">
                                {{ $member['civil_status'] ?? '—' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Contact Number</div>
                            <div class="fw-semibold">
                                {{ $member['contact_number'] ?? '—' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Residency</div>
                            <div class="fw-semibold">
                                {{ $member['residency'] ?? '—' }}
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Residency Start Year</div>
                            <div class="fw-semibold">
                                {{ $member['residency_start_year'] ?? '—' }}
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="text-muted small">Occupation</div>
                            <div class="fw-semibold">
                                {{ $member['occupation'] ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">
                    No additional household members were submitted.
                </p>
            @endforelse
        </div>
    </div>

    {{-- GPS Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Household GPS Location</h5>
        </div>

        <div class="card-body">
            @if ($submission->latitude !== null && $submission->longitude !== null)
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Latitude</div>
                        <div class="fw-semibold">
                            {{ $submission->latitude }}
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Longitude</div>
                        <div class="fw-semibold">
                            {{ $submission->longitude }}
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Accuracy</div>
                        <div class="fw-semibold">
                            {{ $submission->gps_accuracy_meters !== null
                                ? $submission->gps_accuracy_meters . ' meters'
                                : 'Not available' }}
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Captured At</div>
                        <div class="fw-semibold">
                            {{ $submission->gps_captured_at
                                ? $submission->gps_captured_at->format('M d, Y h:i A')
                                : 'Not available' }}
                        </div>
                    </div>
                </div>

                <a href="https://www.google.com/maps?q={{ $submission->latitude }},{{ $submission->longitude }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn btn-outline-primary btn-sm mt-3">
                    <i class="bi bi-geo-alt me-1"></i>
                    View Location on Map
                </a>
            @else
                <div class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i>
                    No household GPS coordinates were submitted.
                </div>
            @endif
        </div>
    </div>

    {{-- Possible Existing Resident Matches --}}
    @if ($possibleMatches->isNotEmpty())
        <div class="card border border-warning shadow-sm mb-4">
            <div class="card-header bg-warning-subtle py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Possible Existing Resident Matches
                </h5>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    One or more submitted persons may already exist in the
                    official resident records. Approval is blocked until these
                    possible matches are reviewed to prevent duplicate records.
                </div>

                @foreach ($possibleMatches as $match)
                    <div class="border rounded p-3 mb-3">
                        <div class="fw-bold mb-2">
                            Submitted Person:
                            {{ $formatName($match['submitted_person'] ?? []) }}
                        </div>

                        @foreach (($match['residents'] ?? []) as $resident)
                            <div class="bg-light rounded p-3 mb-2">
                                <div class="fw-semibold">
                                    {{ trim(implode(' ', array_filter([
                                        $resident->first_name,
                                        $resident->middle_name,
                                        $resident->last_name,
                                        $resident->suffix,
                                    ]))) }}
                                </div>

                                <div class="small text-muted">
                                    Birth Date:
                                    {{ $resident->birth_date
                                        ? $resident->birth_date->format('M d, Y')
                                        : '—' }}
                                </div>

                                <div class="small text-muted">
                                    Resident ID: {{ $resident->id }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Rejection Information --}}
    @if ($status === 'rejected')
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Rejection Reason</div>
            {{ $submission->rejection_reason ?: 'No reason recorded.' }}
        </div>
    @endif

    {{-- Review Actions --}}
    @if ($status === 'pending')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">Review Actions</h5>
            </div>

            <div class="card-body">
                @if ($possibleMatches->isNotEmpty())
                    <div class="alert alert-warning mb-0">
                        Approval is currently unavailable because possible
                        duplicate residents were found. Review and resolve
                        these records before proceeding.
                    </div>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST"
                              action="{{ route('admin.field-personnel-submissions.approve', $submission) }}"
                              onsubmit="return confirm('Approve this submission and create its official household and resident records? This action cannot be undone through this page.');">
                            @csrf

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Approve Submission
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-outline-danger"
                                data-bs-toggle="collapse"
                                data-bs-target="#rejectSubmissionForm"
                                aria-expanded="false"
                                aria-controls="rejectSubmissionForm">
                            <i class="bi bi-x-circle me-1"></i>
                            Reject Submission
                        </button>
                    </div>

                    <div class="collapse mt-3" id="rejectSubmissionForm">
                        <div class="border rounded p-3">
                            <form method="POST"
                                  action="{{ route('admin.field-personnel-submissions.reject', $submission) }}"
                                  onsubmit="return confirm('Are you sure you want to reject this submission?');">
                                @csrf

                                <label for="rejection_reason" class="form-label fw-semibold">
                                    Reason for Rejection
                                </label>

                                <textarea name="rejection_reason"
                                          id="rejection_reason"
                                          class="form-control"
                                          rows="4"
                                          minlength="5"
                                          maxlength="2000"
                                          required
                                          placeholder="Explain why this submission is being rejected...">{{ old('rejection_reason') }}</textarea>

                                @error('rejection_reason')
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <button type="submit" class="btn btn-danger mt-3">
                                    Confirm Rejection
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection