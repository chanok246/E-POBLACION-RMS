
@extends('layouts.app')

@section('title', 'Personnel Account Management')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Personnel Account Management</h2>
            <p class="text-muted mb-0">
                Create and manage Field Personnel accounts for mobile Field Collection.
            </p>
        </div>

        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Attendance Monitoring
        </a>
    </div>

    {{-- System information --}}
    <div class="alert alert-info border-0 shadow-sm">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill fs-5"></i>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
        <div class="card border-success shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <strong>
                    <i class="bi bi-shield-check me-1"></i>
                    Generated Passcode
                </strong>
            </div>

            <div class="card-body">
                <p class="mb-2">
                    Account:
                    <strong>{{ session('generated_account_name') }}</strong>
                </p>

                <p class="text-muted mb-2">
                    Copy and securely provide this passcode to the assigned Field Personnel.
                    It will not be displayed again after leaving or refreshing this page.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <code id="generatedPasscode"
                          class="fs-3 fw-bold px-3 py-2 bg-light border rounded text-dark"
                          style="letter-spacing: 5px;">
                        {{ session('generated_passcode') }}
                    </code>

                    <button type="button"
                            class="btn btn-outline-success"
                            onclick="copyGeneratedPasscode()">
                        <i class="bi bi-clipboard me-1"></i>
                        Copy Passcode
                    </button>
                </div>

                <div id="copyPasscodeMessage" class="small text-success mt-2" aria-live="polite"></div>

                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Do not save or share this passcode through unsecured channels.
                </div>
            </div>
        </div>
    @endif

    {{-- Create account --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-person-plus me-2"></i>
                Register Field Personnel Account
            </h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('attendance.personnel-accounts.store') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="first_name" class="form-label">
                            First Name <span class="text-danger">*</span>
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
                            Last Name <span class="text-danger">*</span>
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
                            Position <span class="text-danger">*</span>
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
                            Username <span class="text-danger">*</span>
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
                        <small class="text-muted">
                            Letters, numbers, underscores, and hyphens only.
                        </small>
                    </div>

                </div>

                <div class="alert alert-light border mt-3 mb-3">
                    <i class="bi bi-key me-1"></i>
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
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-people me-2"></i>
                Registered Field Personnel Accounts
            </h5>

            <span class="badge bg-secondary">
                {{ $accounts->total() }} Account(s)
            </span>
        </div>

        <div class="card-body p-0">

            @if ($accounts->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                        <small class="text-muted">
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
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $account->creator?->name ?? 'Unavailable' }}
                                    </td>

                                    <td>
                                        @if ($account->last_login_at)
                                            {{ $account->last_login_at->format('M d, Y h:i A') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center gap-1">

                                            {{-- Update username --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#usernameForm{{ $account->id }}"
                                                    aria-expanded="false">
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
                                            <div class="bg-light border-top border-bottom p-3">
                                                <form method="POST"
                                                      action="{{ route('attendance.personnel-accounts.username', $account) }}"
                                                      class="row g-2 align-items-end">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="col-md-5">
                                                        <label class="form-label fw-semibold"
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
                    <i class="bi bi-person-lines-fill display-4 text-muted"></i>
                    <h5 class="mt-3">No Field Personnel Accounts Yet</h5>
                    <p class="text-muted mb-0">
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
