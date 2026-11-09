@extends('layouts.app')

@section('title', 'Personnel Account Management')

@section('content')
<style>
    :root,
    [data-bs-theme="light"] {
        --ep-ok-fg: #0b6b3a;
        --ep-ok-bg: rgba(25, 135, 84, .13);
        --ep-ok-bd: rgba(25, 135, 84, .40);

        --ep-warn-fg: #7a4f00;
        --ep-warn-bg: rgba(255, 193, 7, .22);
        --ep-warn-bd: rgba(176, 126, 0, .45);

        --ep-bad-fg: #a4202e;
        --ep-bad-bg: rgba(164, 32, 46, .09);
        --ep-bad-bd: rgba(164, 32, 46, .35);

        --ep-mute-fg: #56615a;
        --ep-mute-bg: rgba(60, 80, 68, .08);
        --ep-mute-bd: rgba(60, 80, 68, .30);

        --ep-head-fg: #3f4a44;
        --ep-note-bg: rgba(25, 135, 84, .07);
        --ep-field-bg: #ffffff;
        --ep-field-bd: #aebab3;
    }

    [data-bs-theme="dark"] {
        --ep-ok-fg: #7ee0a3;
        --ep-ok-bg: rgba(126, 224, 163, .14);
        --ep-ok-bd: rgba(126, 224, 163, .42);

        --ep-warn-fg: #ffd666;
        --ep-warn-bg: rgba(255, 214, 102, .14);
        --ep-warn-bd: rgba(255, 214, 102, .42);

        --ep-bad-fg: #ff9a9a;
        --ep-bad-bg: rgba(255, 154, 154, .12);
        --ep-bad-bd: rgba(255, 154, 154, .40);

        --ep-mute-fg: #a9b9b0;
        --ep-mute-bg: rgba(255, 255, 255, .07);
        --ep-mute-bd: rgba(255, 255, 255, .24);

        --ep-head-fg: #c6d4cc;
        --ep-note-bg: rgba(126, 224, 163, .07);
        --ep-field-bg: rgba(255, 255, 255, .05);
        --ep-field-bd: rgba(255, 255, 255, .22);
    }

    /* ---------- Text helpers ---------- */
    .ep-page .text-secondary,
    .ep-page .text-muted,
    .ep-page .form-text {
        color: var(--ep-mute-fg) !important;
    }

    .ep-page .ep-text-ok   { color: var(--ep-ok-fg) !important; }
    .ep-page .ep-text-warn { color: var(--ep-warn-fg) !important; }
    .ep-page .ep-text-bad  { color: var(--ep-bad-fg) !important; }
    .ep-page .ep-text-mute { color: var(--ep-mute-fg) !important; }

    .ep-page .text-primary { color: var(--ep-ok-fg) !important; }
    .ep-page .text-danger  { color: var(--ep-bad-fg) !important; }

    /* ---------- Badges / status pills ---------- */
    .ep-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: .32rem .75rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.1;
        white-space: nowrap;
    }

    .ep-badge-ok   { color: var(--ep-ok-fg);   background: var(--ep-ok-bg);   border-color: var(--ep-ok-bd); }
    .ep-badge-warn { color: var(--ep-warn-fg); background: var(--ep-warn-bg); border-color: var(--ep-warn-bd); }
    .ep-badge-bad  { color: var(--ep-bad-fg);  background: var(--ep-bad-bg);  border-color: var(--ep-bad-bd); }
    .ep-badge-mute { color: var(--ep-mute-fg); background: var(--ep-mute-bg); border-color: var(--ep-mute-bd); }

    .ep-chip {
        display: inline-block;
        padding: .4rem .9rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        background: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    /* ---------- Panels / notes ---------- */
    .ep-page .card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        color: var(--bs-body-color);
    }

    .ep-page .card-header {
        background: transparent;
        border-bottom: 1px solid var(--bs-border-color);
        color: var(--bs-body-color);
    }

    .ep-note {
        padding: 14px 16px;
        border: 1px solid var(--ep-ok-bd);
        border-left: 4px solid var(--ep-ok-fg);
        border-radius: 10px;
        background: var(--ep-note-bg);
        color: var(--bs-body-color);
    }

    .ep-note .ep-note-icon { color: var(--ep-ok-fg); }

    .ep-subpanel {
        background: var(--bs-tertiary-bg);
        border-top: 1px solid var(--bs-border-color);
        border-bottom: 1px solid var(--bs-border-color);
        color: var(--bs-body-color);
    }

    .ep-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--ep-ok-bd);
        background: var(--ep-ok-bg);
        color: var(--ep-ok-fg);
    }

    .ep-code {
        display: inline-block;
        padding: .5rem 1rem;
        border: 1px solid var(--ep-ok-bd);
        border-radius: 10px;
        background: var(--bs-tertiary-bg);
        color: var(--ep-ok-fg);
    }

    /* ---------- Tables ---------- */
    .ep-page .table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--bs-body-color);
        --bs-table-border-color: var(--bs-border-color);
        --bs-table-hover-bg: var(--ep-ok-bg);
        --bs-table-hover-color: var(--bs-body-color);
    }

    .ep-page .table > thead > tr > th {
        background: var(--bs-tertiary-bg);
        color: var(--ep-head-fg);
        border-bottom: 1px solid var(--bs-border-color);
        font-weight: 700;
    }

    /* ---------- Form controls ---------- */
    .ep-page .form-label {
        color: var(--bs-body-color);
        font-weight: 600;
    }

    .ep-page .form-control,
    .ep-page .form-select {
        background-color: var(--ep-field-bg);
        border-color: var(--ep-field-bd);
        color: var(--bs-body-color);
    }

    .ep-page .form-control::placeholder {
        color: var(--ep-mute-fg);
        opacity: .85;
    }

    .ep-page .form-control:focus,
    .ep-page .form-select:focus {
        background-color: var(--ep-field-bg);
        color: var(--bs-body-color);
        border-color: var(--ep-ok-fg);
        box-shadow: 0 0 0 .2rem var(--ep-ok-bd);
    }

    .ep-page .form-control:disabled,
    .ep-page .form-control[readonly] {
        opacity: .8;
    }

    .ep-page .form-control.is-invalid { border-color: var(--ep-bad-fg); }

    .ep-page .invalid-feedback { color: var(--ep-bad-fg); }

    /* ---------- Buttons (colors come through Bootstrap variables) ---------- */
    .ep-page .btn-outline-secondary {
        --bs-btn-color: var(--ep-head-fg);
        --bs-btn-border-color: var(--ep-field-bd);
        --bs-btn-hover-color: var(--bs-body-bg);
        --bs-btn-hover-bg: var(--ep-head-fg);
        --bs-btn-hover-border-color: var(--ep-head-fg);
    }

    .ep-page .btn-outline-primary,
    .ep-page .btn-outline-success {
        --bs-btn-color: var(--ep-ok-fg);
        --bs-btn-border-color: var(--ep-ok-fg);
        --bs-btn-hover-color: #06261a;
        --bs-btn-hover-bg: var(--ep-ok-fg);
        --bs-btn-hover-border-color: var(--ep-ok-fg);
        --bs-btn-active-color: #06261a;
        --bs-btn-active-bg: var(--ep-ok-fg);
    }

    [data-bs-theme="light"] .ep-page .btn-outline-primary:hover,
    [data-bs-theme="light"] .ep-page .btn-outline-success:hover {
        --bs-btn-hover-color: #ffffff;
    }

    .ep-page .btn-outline-warning {
        --bs-btn-color: var(--ep-warn-fg);
        --bs-btn-border-color: var(--ep-warn-fg);
        --bs-btn-hover-color: #1f1500;
        --bs-btn-hover-bg: #ffc107;
        --bs-btn-hover-border-color: #ffc107;
    }

    .ep-page .btn-outline-danger {
        --bs-btn-color: var(--ep-bad-fg);
        --bs-btn-border-color: var(--ep-bad-fg);
        --bs-btn-hover-color: #ffffff;
        --bs-btn-hover-bg: #b02a37;
        --bs-btn-hover-border-color: #b02a37;
    }

    .ep-page .btn:focus-visible,
    .ep-page a:focus-visible {
        outline: 2px solid var(--ep-ok-fg);
        outline-offset: 2px;
    }

    .ep-page .btn-close { opacity: .75; }
    [data-bs-theme="dark"] .ep-page .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

    /* ---------- Back links ---------- */
    .ep-back {
        color: var(--ep-mute-fg);
        text-decoration: none;
        font-size: .88rem;
    }

    .ep-back:hover { color: var(--ep-ok-fg); }
</style>

<div class="container-fluid py-4 ep-page">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Personnel Account Management</h2>
            <p class="text-secondary mb-0">
                Create and manage Field Personnel accounts for mobile Field Collection.
            </p>
        </div>

        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Attendance Monitoring
        </a>
    </div>

    {{-- System information --}}
    <div class="ep-note mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill fs-5 ep-note-icon"></i>
            <div>
                <strong>Separate Personnel Accounts</strong>
                <p class="mb-0">
                    These accounts are exclusively for authorized Field Collection users.
                    Creating an account here does not register the person in Attendance Monitoring.
                    Attendance Personnel registration and Field Personnel accounts are independent.
                </p>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="ep-note alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-info-circle ep-note-icon me-1"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Generated passcode: shown only after creation or regeneration --}}
    @if (session('generated_passcode'))
        <div class="card shadow-sm mb-4" style="border-color: var(--ep-ok-fg) !important;">
            <div class="card-header" style="background: var(--ep-ok-bg);">
                <strong class="ep-text-ok">
                    <i class="bi bi-shield-check me-1"></i>
                    Generated Passcode
                </strong>
            </div>

            <div class="card-body">
                <p class="mb-2">
                    Account:
                    <strong>{{ session('generated_account_name') }}</strong>
                </p>

                <p class="text-secondary mb-3">
                    Copy and securely provide this passcode to the assigned Field Personnel.
                    It will not be displayed again after leaving or refreshing this page.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <code id="generatedPasscode"
                          class="ep-code fs-3 fw-bold"
                          style="letter-spacing: 5px;">{{ session('generated_passcode') }}</code>

                    <button type="button"
                            class="btn btn-outline-success"
                            onclick="copyGeneratedPasscode()">
                        <i class="bi bi-clipboard me-1"></i>
                        Copy Passcode
                    </button>
                </div>

                <div id="copyPasscodeMessage" class="small ep-text-ok mt-2" aria-live="polite"></div>

                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Do not save or share this passcode through unsecured channels.
                </div>
            </div>
        </div>
    @endif

    {{-- Create account --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-person-plus me-2 ep-text-ok"></i>
                Register Field Personnel Account
            </h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('attendance.personnel-accounts.store') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="first_name" class="form-label">
                            First Name <span class="ep-text-bad">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="first_name"
                               name="first_name"
                               value="{{ old('first_name') }}"
                               maxlength="100"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text"
                               class="form-control"
                               id="middle_name"
                               name="middle_name"
                               value="{{ old('middle_name') }}"
                               maxlength="100">
                    </div>

                    <div class="col-md-4">
                        <label for="last_name" class="form-label">
                            Last Name <span class="ep-text-bad">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="last_name"
                               name="last_name"
                               value="{{ old('last_name') }}"
                               maxlength="100"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label for="suffix" class="form-label">Suffix</label>
                        <input type="text"
                               class="form-control"
                               id="suffix"
                               name="suffix"
                               value="{{ old('suffix') }}"
                               maxlength="20"
                               placeholder="Jr., Sr., III">
                    </div>

                    <div class="col-md-4">
                        <label for="position" class="form-label">
                            Position <span class="ep-text-bad">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="position"
                               name="position"
                               value="{{ old('position') }}"
                               maxlength="100"
                               placeholder="e.g. BHW, Enumerator"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label for="username" class="form-label">
                            Username <span class="ep-text-bad">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               value="{{ old('username') }}"
                               maxlength="100"
                               pattern="[A-Za-z0-9_-]+"
                               autocomplete="off"
                               required>
                        <small class="text-secondary">
                            Letters, numbers, underscores, and hyphens only.
                        </small>
                    </div>

                </div>

                <div class="ep-subpanel rounded-3 border p-3 my-3">
                    <i class="bi bi-key ep-text-ok me-1"></i>
                    The system will automatically generate a six-digit numeric passcode.
                    The passcode will be displayed once after successful creation.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>
                    Create Field Personnel Account
                </button>
            </form>
        </div>
    </div>

    {{-- Account list --}}
    <div class="card shadow-sm">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-people me-2 ep-text-ok"></i>
                Registered Field Personnel Accounts
            </h5>

            <span class="ep-badge ep-badge-mute">
                {{ $accounts->total() }} Account(s)
            </span>
        </div>

        <div class="card-body p-0">

            @if ($accounts->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="px-3">Personnel</th>
                                <th>Username</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Last Login</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($accounts as $account)
                                <tr>
                                    <td class="px-3">
                                        <div class="fw-semibold">
                                            {{ $account->full_name }}
                                        </div>
                                        <small class="text-secondary">
                                            Account ID: {{ $account->id }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="fw-semibold">
                                            {{ $account->username }}
                                        </span>
                                    </td>

                                    <td>{{ $account->position }}</td>

                                    <td>
                                        @if ($account->account_active)
                                            <span class="ep-badge ep-badge-ok">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="ep-badge ep-badge-mute">
                                                <i class="bi bi-slash-circle"></i> Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $account->creator?->name ?? 'Unavailable' }}
                                    </td>

                                    <td>
                                        @if ($account->last_login_at)
                                            {{ $account->last_login_at->format('M d, Y h:i A') }}
                                        @else
                                            <span class="text-secondary">Never</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center gap-1">

                                            {{-- Update username --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#usernameForm{{ $account->id }}"
                                                    aria-expanded="false"
                                                    aria-controls="usernameForm{{ $account->id }}">
                                                <i class="bi bi-pencil-square"></i>
                                                Username
                                            </button>

                                            {{-- Regenerate passcode --}}
                                            <form method="POST"
                                                  action="{{ route('attendance.personnel-accounts.passcode', $account) }}"
                                                  onsubmit="return confirm('Regenerate the passcode for {{ addslashes($account->full_name) }}? The previous passcode will immediately become invalid.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                    Regenerate
                                                </button>
                                            </form>

                                            {{-- Activate / Deactivate --}}
                                            @if ($account->account_active)
                                                <form method="POST"
                                                      action="{{ route('attendance.personnel-accounts.deactivate', $account) }}"
                                                      onsubmit="return confirm('Deactivate this account? The personnel will no longer be able to access Field Collection.');">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-person-x"></i>
                                                        Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('attendance.personnel-accounts.activate', $account) }}"
                                                      onsubmit="return confirm('Activate this Field Personnel account?');">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-person-check"></i>
                                                        Activate
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>
                                </tr>

                                {{-- Username update form --}}
                                <tr class="border-0">
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="collapse" id="usernameForm{{ $account->id }}">
                                            <div class="ep-subpanel p-3">
                                                <form method="POST"
                                                      action="{{ route('attendance.personnel-accounts.username', $account) }}"
                                                      class="row g-2 align-items-end">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="col-md-5">
                                                        <label class="form-label"
                                                               for="username{{ $account->id }}">
                                                            New Username
                                                        </label>
                                                        <input type="text"
                                                               class="form-control"
                                                               id="username{{ $account->id }}"
                                                               name="username"
                                                               value="{{ $account->username }}"
                                                               maxlength="100"
                                                               pattern="[A-Za-z0-9_-]+"
                                                               required>
                                                    </div>

                                                    <div class="col-auto">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-save me-1"></i>
                                                            Save Username
                                                        </button>
                                                    </div>

                                                    <div class="col-auto">
                                                        <button type="button"
                                                                class="btn btn-outline-secondary"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#usernameForm{{ $account->id }}">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $accounts->links() }}
                </div>

            @else
                <div class="text-center py-5 px-3">
                    <i class="bi bi-person-lines-fill display-4 ep-text-mute"></i>
                    <h5 class="mt-3">No Field Personnel Accounts Yet</h5>
                    <p class="text-secondary mb-0">
                        Use the registration form above to create the first account.
                    </p>
                </div>
            @endif

        </div>
    </div>

</div>

<script>
    function copyGeneratedPasscode() {
        const passcodeElement = document.getElementById('generatedPasscode');
        const messageElement = document.getElementById('copyPasscodeMessage');

        if (!passcodeElement || !messageElement) {
            return;
        }

        const passcode = passcodeElement.textContent.trim();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(passcode)
                .then(() => {
                    messageElement.textContent = 'Passcode copied successfully.';
                })
                .catch(() => {
                    messageElement.textContent = 'Unable to copy automatically. Please select and copy the passcode.';
                });
        } else {
            const range = document.createRange();
            range.selectNodeContents(passcodeElement);

            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);

            messageElement.textContent = 'Select the highlighted passcode and copy it.';
        }
    }
</script>
@endsection
