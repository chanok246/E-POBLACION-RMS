@extends('field-personnel.layouts.app')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">My Submissions</h4>
            <p class="text-muted mb-0">
                Track the household information you have submitted for verification.
            </p>
        </div>

        <a href="{{ route('field-personnel.households.create') }}"
           class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            New Household
        </a>
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

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-4">
                    <div class="text-muted small">Total</div>
                    <div class="fs-4 fw-bold">
                        {{ $submissions->total() }}
                    </div>
                </div>

                <div class="col-4">
                    <div class="text-muted small">Pending</div>
                    <div class="fs-4 fw-bold text-warning">
                        {{ $submissions->getCollection()->where('status', 'pending')->count() }}
                    </div>
                </div>

                <div class="col-4">
                    <div class="text-muted small">This Page</div>
                    <div class="fs-4 fw-bold">
                        {{ $submissions->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @forelse ($submissions as $submission)
        @php
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
        @endphp

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">

                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <div class="text-muted small">Submission Reference</div>
                        <div class="fw-bold">
                            {{ $submission->submission_reference }}
                        </div>
                    </div>

                    <span class="badge bg-{{ $statusClass }} text-wrap">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted small">Date Submitted</div>
                        <div class="fw-semibold">
                            {{ $submission->submitted_at
                                ? $submission->submitted_at->format('M d, Y h:i A')
                                : 'Not available' }}
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="text-muted small">Last Reviewed</div>
                        <div class="fw-semibold">
                            {{ $submission->reviewed_at
                                ? $submission->reviewed_at->format('M d, Y h:i A')
                                : 'Not yet reviewed' }}
                        </div>
                    </div>
                </div>

                @if ($status === 'rejected' && $submission->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0">
                        <div class="fw-bold mb-1">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Reason for Rejection
                        </div>
                        <div class="small">
                            {{ $submission->rejection_reason }}
                        </div>
                    </div>
                @elseif ($status === 'approved')
                    <div class="alert alert-success mt-3 mb-0">
                        <div class="fw-bold mb-1">
                            <i class="bi bi-check-circle me-1"></i>
                            Submission Approved
                        </div>

                        @if ($submission->approvedHousehold)
                            <div class="small">
                                Official Household Number:
                                <strong>
                                    {{ $submission->approvedHousehold->household_number }}
                                </strong>
                            </div>
                        @else
                            <div class="small">
                                Your submission has been approved.
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-muted small mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        This submission is awaiting review by the authorized
                        Barangay Captain or Secretary. It is not yet an official
                        household record.
                    </div>
                @endif

            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <h5 class="fw-bold mt-3">No Submissions Yet</h5>
                <p class="text-muted mb-3">
                    Your submitted household information will appear here.
                </p>

                <a href="{{ route('field-personnel.households.create') }}"
                   class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Submit Household Information
                </a>
            </div>
        </div>
    @endforelse

    @if ($submissions->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $submissions->links() }}
        </div>
    @endif

</div>
@endsection