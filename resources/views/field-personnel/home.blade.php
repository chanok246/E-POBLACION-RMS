@extends('field-personnel.layouts.app')

@section('title', 'Field Personnel Dashboard')

@push('styles')
<style>
    /* Dashboard-only styles. Built on the --fc-* variables of the
       Field Personnel layout (no Bootstrap dependency). */

    .fd-hero {
        position: relative;
        overflow: hidden;
        padding: 22px 20px;
        border-radius: 18px;
        color: #fff;
        background: linear-gradient(135deg, var(--fc-green-dark) 0%, var(--fc-green) 100%);
        box-shadow: var(--fc-shadow);
    }

    .fd-hero::after {
        content: "";
        position: absolute;
        right: -50px;
        top: -50px;
        width: 190px;
        height: 190px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .08);
        pointer-events: none;
    }

    .fd-greeting {
        margin: 0 0 4px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .78);
    }

    .fd-title {
        margin: 0 0 8px;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
    }

    .fd-hero-text {
        position: relative;
        margin: 0 0 18px;
        max-width: 560px;
        font-size: 13px;
        line-height: 1.6;
        color: rgba(255, 255, 255, .9);
    }

    .fd-cta {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        min-height: 50px;
        padding: 12px 18px;
        border-radius: 12px;
        background: #fff;
        color: var(--fc-green-dark);
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .14);
        transition: transform .15s, box-shadow .15s;
    }

    .fd-cta:active {
        transform: translateY(1px);
    }

    .fd-cta svg {
        width: 20px;
        height: 20px;
    }

    .fd-section-title {
        margin: 26px 0 12px;
        color: var(--fc-green-dark);
        font-size: 16px;
        font-weight: 800;
    }

    /* Quick actions */
    .fd-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .fd-tile {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-height: 128px;
        padding: 16px;
        background: var(--fc-white);
        border: 1px solid var(--fc-border);
        border-radius: var(--fc-radius);
        box-shadow: var(--fc-shadow);
        text-decoration: none;
        color: var(--fc-text);
        transition: transform .15s, border-color .2s;
    }

    .fd-tile:hover {
        border-color: #c7dccf;
    }

    .fd-tile:active {
        transform: scale(.985);
    }

    .fd-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--fc-green-light);
        color: var(--fc-green);
    }

    .fd-icon svg {
        width: 22px;
        height: 22px;
    }

    .fd-icon.is-blue   { background: #e8f2fb; color: #2468a2; }
    .fd-icon.is-amber  { background: #fff5dc; color: #a06a00; }
    .fd-icon.is-purple { background: #f0eafa; color: #6a45a8; }

    .fd-tile-title {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.3;
    }

    .fd-tile-text {
        margin: 2px 0 0;
        color: var(--fc-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    /* Recent submissions */
    .fd-card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .fd-card-head .fc-card-title {
        margin-bottom: 3px;
    }

    .fd-link-button {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 7px 14px;
        border: 1px solid var(--fc-green);
        border-radius: 9px;
        color: var(--fc-green);
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .fd-link-button:hover {
        background: var(--fc-green);
        color: #fff;
    }

    .fd-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .fd-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 6px 12px;
        align-items: center;
        padding: 13px 0;
        border-top: 1px solid var(--fc-border);
    }

    .fd-row:first-child {
        border-top: 0;
        padding-top: 2px;
    }

    .fd-ref {
        font-size: 14px;
        font-weight: 800;
        word-break: break-word;
    }

    .fd-date {
        grid-column: 1;
        color: var(--fc-muted);
        font-size: 12px;
    }

    .fd-status {
        grid-column: 2;
        grid-row: 1;
        justify-self: end;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .fd-status.is-pending  { background: #fff5dc; color: #8a5a00; }
    .fd-status.is-approved { background: #e3f4ea; color: #17613e; }
    .fd-status.is-rejected { background: #fdebed; color: #9f2332; }

    .fd-status::before {
        content: "";
        display: inline-block;
        width: 7px;
        height: 7px;
        margin-right: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .fd-view {
        grid-column: 2;
        grid-row: 2;
        justify-self: end;
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 5px 13px;
        border: 1px solid var(--fc-border);
        border-radius: 8px;
        color: var(--fc-text);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .fd-view:hover {
        background: var(--fc-background);
    }

    /* Empty state */
    .fd-empty {
        padding: 26px 10px 10px;
        text-align: center;
    }

    .fd-empty .fd-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 12px;
        border-radius: 50%;
    }

    .fd-empty .fd-icon svg {
        width: 28px;
        height: 28px;
    }

    .fd-empty h3 {
        margin: 0 0 5px;
        font-size: 15px;
        font-weight: 800;
    }

    .fd-empty p {
        margin: 0 auto 16px;
        max-width: 320px;
        color: var(--fc-muted);
        font-size: 13px;
    }

    /* Notice */
    .fd-notice {
        display: flex;
        gap: 12px;
        margin-top: 18px;
        padding: 14px 15px;
        border: 1px solid #c9e4f5;
        border-radius: 12px;
        background: #eef7fd;
        color: #245c83;
        font-size: 12.5px;
        line-height: 1.6;
    }

    .fd-notice svg {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        margin-top: 1px;
    }

    /* Tablet and desktop */
    @media (min-width: 700px) {
        .fd-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
            padding: 30px 32px;
        }

        .fd-hero-copy {
            position: relative;
            z-index: 1;
        }

        .fd-hero-text {
            margin-bottom: 0;
        }

        .fd-cta {
            width: auto;
            flex-shrink: 0;
            padding: 13px 26px;
            z-index: 1;
        }

        .fd-title {
            font-size: 26px;
        }

        .fd-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .fd-row {
            grid-template-columns: 1.2fr 1fr auto auto;
            gap: 14px;
        }

        .fd-date,
        .fd-status,
        .fd-view {
            grid-column: auto;
            grid-row: auto;
        }
    }
</style>
@endpush

@section('content')

@php
    $fpUser = auth('field_personnel')->user();
@endphp

{{-- Main field operation --}}
<section class="fd-hero" aria-labelledby="fd-title">
    <div class="fd-hero-copy">
        <p class="fd-greeting">
            Household Field Collection
            @if ($fpUser && !empty($fpUser->full_name))
                &middot; {{ $fpUser->full_name }}
            @endif
        </p>

        <h1 class="fd-title" id="fd-title">Field Collection Dashboard</h1>

        <p class="fd-hero-text">
            Manage household field submissions and monitor their verification status.
            Record household and resident information and capture the household's
            GPS location using your mobile device.
        </p>
    </div>

    <a href="{{ route('field-personnel.households.create') }}" class="fd-cta">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M12 13v4M10 15h4"/></svg>
        New Household Submission
    </a>
</section>

{{-- Quick access --}}
<h2 class="fd-section-title">Quick Access</h2>

<div class="fd-grid">

    <a href="{{ route('field-personnel.households.create') }}" class="fd-tile">
        <span class="fd-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M12 13v4M10 15h4"/></svg>
        </span>
        <span>
            <h3 class="fd-tile-title">New Collection</h3>
            <p class="fd-tile-text">Submit household information</p>
        </span>
    </a>

    <a href="{{ route('field-personnel.submissions.index') }}" class="fd-tile">
        <span class="fd-icon is-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
        </span>
        <span>
            <h3 class="fd-tile-title">My Submissions</h3>
            <p class="fd-tile-text">View submitted household records</p>
        </span>
    </a>

    <a href="{{ route('field-personnel.vouchers.verify') }}" class="fd-tile">
        <span class="fd-icon is-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h3M20 14v6M14 20h3"/></svg>
        </span>
        <span>
            <h3 class="fd-tile-title">Voucher Verification</h3>
            <p class="fd-tile-text">Open voucher verification</p>
        </span>
    </a>

    <a href="{{ route('field-personnel.tasks.index') }}" class="fd-tile">
        <span class="fd-icon is-amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        </span>
        <span>
            <h3 class="fd-tile-title">Assigned Tasks</h3>
            <p class="fd-tile-text">View your assigned tasks</p>
        </span>
    </a>

</div>

{{-- Recent submissions --}}
<h2 class="fd-section-title">Recent Activity</h2>

<div class="fc-card">

    <div class="fd-card-head">
        <div>
            <h3 class="fc-card-title">Recent Household Submissions</h3>
            <p class="fc-card-description">
                Monitor the review status of your submitted records.
            </p>
        </div>

        <a href="{{ route('field-personnel.submissions.index') }}" class="fd-link-button">
            View All
        </a>
    </div>

    @if (isset($recentSubmissions) && $recentSubmissions->isNotEmpty())

        <ul class="fd-list">
            @foreach ($recentSubmissions as $submission)
                @php
                    $status = strtolower($submission->status ?? 'pending');

                    $statusClass = match ($status) {
                        'approved' => 'is-approved',
                        'rejected' => 'is-rejected',
                        default => 'is-pending',
                    };

                    $statusLabel = match ($status) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => 'Pending for Verification',
                    };
                @endphp

                <li class="fd-row">
                    <span class="fd-ref">
                        {{ $submission->submission_reference ?? 'N/A' }}
                    </span>

                    <span class="fd-date">
                        {{ $submission->submitted_at
                            ? $submission->submitted_at->format('M d, Y h:i A')
                            : 'N/A' }}
                    </span>

                    <span class="fd-status {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                    <a href="{{ route('field-personnel.submissions.index') }}" class="fd-view">
                        View
                    </a>
                </li>
            @endforeach
        </ul>

    @else

        <div class="fd-empty">
            <span class="fd-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/></svg>
            </span>

            <h3>No submissions yet</h3>

            <p>Your household submissions will appear here after you submit them.</p>

            <a href="{{ route('field-personnel.households.create') }}"
               class="fc-button fc-button-primary">
                Start Household Collection
            </a>
        </div>

    @endif

</div>

{{-- Verification reminder --}}
<div class="fd-notice" role="note">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>

    <div>
        <strong>Important:</strong>
        Submitted household information remains pending until reviewed and approved
        by an authorized Barangay Captain or Secretary. Submission does not
        automatically create an official household or resident record.
    </div>
</div>

@endsection