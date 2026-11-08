@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Field Personnel Submissions</h4>
            <p class="text-muted mb-0">
                Review household information submitted by Field Personnel.
            </p>
        </div>
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Submissions</div>
                    <div class="fs-3 fw-bold">
                        {{ $counts['total'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Pending</div>
                    <div class="fs-3 fw-bold text-warning">
                        {{ $counts['pending'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Approved</div>
                    <div class="fs-3 fw-bold text-success">
                        {{ $counts['approved'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Rejected</div>
                    <div class="fs-3 fw-bold text-danger">
                        {{ $counts['rejected'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET"
                  action="{{ route('admin.field-personnel-submissions.index') }}"
                  class="row g-2 align-items-end">

                <div class="col-12 col-md-4">
                    <label for="status" class="form-label fw-semibold">
                        Filter by Status
                    </label>

                    <select name="status" id="status" class="form-select">
                        <option value="">All Submissions</option>
                        <option value="pending"
                            {{ request('status') === 'pending' ? 'selected' : '' }}>
                            Pending for Verification
                        </option>
                        <option value="approved"
                            {{ request('status') === 'approved' ? 'selected' : '' }}>
                            Approved
                        </option>
                        <option value="rejected"
                            {{ request('status') === 'rejected' ? 'selected' : '' }}>
                            Rejected
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>
                        Apply Filter
                    </button>

                    <a href="{{ route('admin.field-personnel-submissions.index') }}"
                       class="btn btn-outline-secondary">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Submitted Household Records</h5>
        </div>

        <div class="card-body p-0">

            @if ($submissions->isEmpty())
                <div class="text-center py-5 px-3">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <h5 class="fw-bold mt-3">No Submissions Found</h5>
                    <p class="text-muted mb-0">
                        There are no household submissions matching this filter.
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">Reference</th>
                                <th>Submitted By</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th>Reviewed By</th>
                                <th class="text-end px-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($submissions as $submission)
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
                                        default => 'Pending',
                                    };
                                @endphp

                                <tr>
                                    <td class="px-3">
                                        <div class="fw-semibold">
                                            {{ $submission->submission_reference }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $submission->submitted_by_name }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $submission->submitted_by_position ?: 'Field Personnel' }}
                                        </div>
                                    </td>

                                    <td>
                                        {{ $submission->submitted_at
                                            ? $submission->submitted_at->format('M d, Y h:i A')
                                            : '—' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $submission->reviewer?->name ?? '—' }}
                                    </td>

                                    <td class="text-end px-3">
                                        <a href="{{ route('admin.field-personnel-submissions.show', $submission) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        @if ($submissions->hasPages())
            <div class="card-footer bg-white">
                {{ $submissions->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection