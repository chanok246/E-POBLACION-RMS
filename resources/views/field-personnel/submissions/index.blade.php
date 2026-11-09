
@extends('field-personnel.layouts.app')

@section('title', 'My Submissions')

@section('content')
@php
    $isPaginator = $submissions instanceof \Illuminate\Pagination\AbstractPaginator;
    $submissionItems = $isPaginator
        ? $submissions->getCollection()
        : collect($submissions);

    $totalSubmissions = $isPaginator
        ? $submissions->total()
        : $submissionItems->count();

    $pendingCount = $submissionItems->where('status', 'pending')->count();
@endphp

@push('styles')
<style>
    .ms-page {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 12px 0 32px;
        color: #24352c;
    }

    .ms-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .ms-title {
        margin: 0 0 6px;
        font-size: clamp(22px, 4vw, 28px);
        font-weight: 800;
        letter-spacing: -.5px;
        color: #183d2b;
    }

    .ms-subtitle {
        margin: 0;
        color: #718078;
        font-size: 14px;
        line-height: 1.6;
    }

    .ms-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        min-height: 43px;
        padding: 11px 17px;
        border: 0;
        border-radius: 10px;
        background: #18794e;
        color: #fff !important;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        transition: background .2s, transform .2s;
    }

    .ms-button:hover {
        background: #12633f;
        transform: translateY(-1px);
    }

    .ms-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .ms-stat {
        padding: 18px;
        border: 1px solid #e6eee8;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(20, 55, 35, .04);
    }

    .ms-stat-label {
        margin-bottom: 7px;
        color: #77857c;
        font-size: 12px;
        font-weight: 600;
    }

    .ms-stat-value {
        color: #193e2c;
        font-size: 27px;
        font-weight: 800;
        line-height: 1.2;
    }

    .ms-stat.pending .ms-stat-value {
        color: #b7791f;
    }

    .ms-alert {
        padding: 13px 16px;
        margin-bottom: 18px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.6;
    }

    .ms-alert.success {
        color: #17613d;
        background: #eaf7ef;
        border: 1px solid #ccebd7;
    }

    .ms-alert.error {
        color: #a42c35;
        background: #fff0f0;
        border: 1px solid #f3cccc;
    }

    .ms-list {
        display: grid;
        gap: 15px;
    }

    .ms-card {
        padding: 20px;
        border: 1px solid #e6eee8;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 3px 12px rgba(20, 55, 35, .045);
    }

    .ms-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        padding-bottom: 16px;
        border-bottom: 1px solid #edf1ed;
    }

    .ms-label {
        display: block;
        margin-bottom: 5px;
        color: #819087;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }

    .ms-reference {
        color: #234a35;
        font-size: 15px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .ms-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 800;
    }

    .ms-badge.pending {
        color: #94620d;
        background: #fff5d9;
    }

    .ms-badge.approved {
        color: #17613d;
        background: #e5f6eb;
    }

    .ms-badge.rejected {
        color: #a42c35;
        background: #fdeaea;
    }

    .ms-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        padding-top: 17px;
    }

    .ms-detail-value {
        color: #35483d;
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .ms-status-message {
        margin-top: 17px;
        padding: 13px 15px;
        border-radius: 10px;
        font-size: 12px;
        line-height: 1.65;
    }

    .ms-status-message.pending {
        color: #68776e;
        background: #f5f8f5;
        border: 1px solid #e8eee9;
    }

    .ms-status-message.approved {
        color: #17613d;
        background: #eaf7ef;
        border: 1px solid #ccebd7;
    }

    .ms-status-message.rejected {
        color: #a42c35;
        background: #fff0f0;
        border: 1px solid #f3cccc;
    }

    .ms-empty {
        padding: 48px 20px;
        border: 1px dashed #cbd9ce;
        border-radius: 15px;
        background: #fff;
        text-align: center;
    }

    .ms-empty-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 50%;
        background: #edf7f0;
        color: #268052;
        font-size: 28px;
    }

    .ms-empty h3 {
        margin: 0 0 8px;
        color: #234a35;
        font-size: 18px;
        font-weight: 800;
    }

    .ms-empty p {
        margin: 0 auto 19px;
        max-width: 390px;
        color: #78877e;
        font-size: 13px;
        line-height: 1.6;
    }

    .ms-pagination {
        display: flex;
        justify-content: center;
        margin-top: 24px;
        overflow-x: auto;
    }

    .ms-pagination nav {
        max-width: 100%;
    }

    .ms-pagination svg {
        width: 18px;
        height: 18px;
    }

    @media (max-width: 640px) {
        .ms-page {
            padding-top: 5px;
        }

        .ms-header {
            align-items: stretch;
            flex-direction: column;
        }

        .ms-header .ms-button {
            width: 100%;
        }

        .ms-stats {
            gap: 9px;
        }

        .ms-stat {
            padding: 13px 10px;
        }

        .ms-stat-label {
            font-size: 10px;
        }

        .ms-stat-value {
            font-size: 22px;
        }

        .ms-card {
            padding: 16px;
        }

        .ms-details {
            gap: 14px 10px;
        }

        .ms-detail-value {
            font-size: 12px;
        }
    }

    @media (max-width: 360px) {
        .ms-stats {
            grid-template-columns: 1fr;
        }

        .ms-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ms-stat-label {
            margin: 0;
        }
    }
</style>
@endpush

<div class="ms-page">

    <div class="ms-header">
        <div>
            <h1 class="ms-title">My Submissions</h1>
            <p class="ms-subtitle">
                Track the household information you have submitted for verification.
            </p>
        </div>

        <a href="{{ route('field-personnel.households.create') }}"
           class="ms-button">
            <span aria-hidden="true">＋</span>
            New Household
        </a>
    </div>

    @if (session('success'))
        <div class="ms-alert success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="ms-alert error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="ms-stats">
        <div class="ms-stat">
            <div class="ms-stat-label">Total Submissions</div>
            <div class="ms-stat-value">{{ $totalSubmissions }}</div>
        </div>

        <div class="ms-stat pending">
            <div class="ms-stat-label">Pending on This Page</div>
            <div class="ms-stat-value">{{ $pendingCount }}</div>
        </div>

        <div class="ms-stat">
            <div class="ms-stat-label">Displayed</div>
            <div class="ms-stat-value">{{ $submissionItems->count() }}</div>
        </div>
    </div>

    @forelse ($submissionItems as $submission)
        @php
            $status = strtolower($submission->status ?? 'pending');

            $statusClass = match ($status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                default => 'pending',
            };

            $statusLabel = match ($status) {
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default => 'Pending for Verification',
            };
        @endphp

        <article class="ms-card">
            <div class="ms-card-top">
                <div>
                    <span class="ms-label">Submission Reference</span>
                    <div class="ms-reference">
                        {{ $submission->submission_reference ?: 'Reference not available' }}
                    </div>
                </div>

                <span class="ms-badge {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="ms-details">
                <div>
                    <span class="ms-label">Date Submitted</span>
                    <div class="ms-detail-value">
                        {{ $submission->submitted_at
                            ? \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('M d, Y h:i A')
                            : 'Not available' }}
                    </div>
                </div>

                <div>
                    <span class="ms-label">Last Reviewed</span>
                    <div class="ms-detail-value">
                        {{ $submission->reviewed_at
                            ? \Illuminate\Support\Carbon::parse($submission->reviewed_at)->format('M d, Y h:i A')
                            : 'Not yet reviewed' }}
                    </div>
                </div>
            </div>

            @if ($status === 'rejected')
                <div class="ms-status-message rejected">
                    <strong>Submission Rejected</strong><br>
                    {{ $submission->rejection_reason ?: 'No rejection reason was provided.' }}
                </div>

            @elseif ($status === 'approved')
                <div class="ms-status-message approved">
                    <strong>Submission Approved</strong><br>

                    @if ($submission->approvedHousehold)
                        Official Household Number:
                        <strong>{{ $submission->approvedHousehold->household_number }}</strong>
                    @else
                        Your submission has been approved.
                    @endif
                </div>

            @else
                <div class="ms-status-message pending">
                    Your submission is awaiting review by the authorized Barangay Captain or Secretary.
                    It is not yet an official household record.
                </div>
            @endif
        </article>
    @empty
        <div class="ms-empty">
            <div class="ms-empty-icon" aria-hidden="true">▤</div>

            <h3>No Submissions Yet</h3>

            <p>
                Your submitted household information will appear here once it has been successfully submitted.
            </p>

            <a href="{{ route('field-personnel.households.create') }}"
               class="ms-button">
                ＋ Submit Household Information
            </a>
        </div>
    @endforelse

    @if ($isPaginator && $submissions->hasPages())
        <div class="ms-pagination">
            {{ $submissions->links() }}
        </div>
    @endif

</div>
@endsection
