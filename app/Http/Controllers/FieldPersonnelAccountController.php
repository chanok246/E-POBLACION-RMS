<?php

namespace App\Http\Controllers;

use App\Models\FieldPersonnelAccount;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FieldPersonnelAccountController extends Controller
{
    public function index(): View
    {
        $this->authorizeSecretary();

        $accounts = FieldPersonnelAccount::query()
            ->with('creator')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return view('attendance.personnel-accounts.index', [
            'accounts' => $accounts,
        ]);
    }

    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorizeSecretary();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'position' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('field_personnel_accounts', 'username'),
            ],
        ]);

        $passcode = $this->generatePasscode();

        $account = DB::transaction(function () use (
            $validated,
            $passcode,
            $auditLog,
            $request
        ) {
            $account = new FieldPersonnelAccount();

            $account->first_name = $validated['first_name'];
            $account->middle_name = $validated['middle_name'] ?? null;
            $account->last_name = $validated['last_name'];
            $account->suffix = $validated['suffix'] ?? null;
            $account->position = $validated['position'];
            $account->username = $validated['username'];
            $account->account_active = true;
            $account->created_by = $request->user()->id;

            $account->setLoginPassword($passcode);
            $account->save();

            $auditLog->log(
                action: 'CREATE',
                module: 'Field Personnel Accounts',
                record: $account,
                newValues: [
                    'name' => $account->full_name,
                    'username' => $account->username,
                    'position' => $account->position,
                    'account_active' => true,
                ],
                description: 'Created a Field Personnel account. The generated passcode was not recorded.'
            );

            return $account;
        });

        return redirect()
            ->route('attendance.personnel-accounts.index')
            ->with('generated_passcode', $passcode)
            ->with('generated_account_name', $account->full_name)
            ->with('success', 'Field Personnel account created successfully. Copy the generated passcode now.');
    }

    public function updateUsername(
        Request $request,
        FieldPersonnelAccount $account,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorizeSecretary();

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('field_personnel_accounts', 'username')
                    ->ignore($account->id),
            ],
        ]);

        $oldUsername = $account->username;

        if ($oldUsername === $validated['username']) {
            return back()->with('info', 'The username has not changed.');
        }

        DB::transaction(function () use (
            $account,
            $validated,
            $oldUsername,
            $auditLog
        ) {
            $account->username = $validated['username'];
            $account->save();

            $auditLog->log(
                action: 'UPDATE',
                module: 'Field Personnel Accounts',
                record: $account,
                oldValues: [
                    'username' => $oldUsername,
                ],
                newValues: [
                    'username' => $account->username,
                ],
                description: 'Updated a Field Personnel account username.'
            );
        });

        return redirect()
            ->route('attendance.personnel-accounts.index')
            ->with('success', 'Username updated successfully.');
    }

    public function regeneratePasscode(
        Request $request,
        FieldPersonnelAccount $account,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorizeSecretary();

        $passcode = $this->generatePasscode();

        DB::transaction(function () use (
            $account,
            $passcode,
            $auditLog
        ) {
            $account->setLoginPassword($passcode);
            $account->save();

            $auditLog->log(
                action: 'UPDATE',
                module: 'Field Personnel Accounts',
                record: $account,
                newValues: [
                    'passcode_regenerated' => true,
                    'password_updated_at' => $account->password_updated_at?->toDateTimeString(),
                ],
                description: 'Regenerated the Field Personnel account passcode. The previous passcode was invalidated. The new passcode was not recorded.'
            );
        });

        return redirect()
            ->route('attendance.personnel-accounts.index')
            ->with('generated_passcode', $passcode)
            ->with('generated_account_name', $account->full_name)
            ->with('success', 'Passcode regenerated successfully. The previous passcode is no longer valid. Copy the new passcode now.');
    }

    public function activate(
        Request $request,
        FieldPersonnelAccount $account,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorizeSecretary();

        if ($account->account_active) {
            return back()->with('info', 'This account is already active.');
        }

        DB::transaction(function () use ($account, $auditLog) {
            $account->account_active = true;
            $account->save();

            $auditLog->log(
                action: 'ACTIVATE',
                module: 'Field Personnel Accounts',
                record: $account,
                oldValues: [
                    'account_active' => false,
                ],
                newValues: [
                    'account_active' => true,
                ],
                description: 'Activated a Field Personnel account.'
            );
        });

        return redirect()
            ->route('attendance.personnel-accounts.index')
            ->with('success', 'Field Personnel account activated successfully.');
    }

    public function deactivate(
        Request $request,
        FieldPersonnelAccount $account,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->authorizeSecretary();

        if (! $account->account_active) {
            return back()->with('info', 'This account is already inactive.');
        }

        DB::transaction(function () use ($account, $auditLog) {
            $account->account_active = false;
            $account->save();

            $auditLog->log(
                action: 'DEACTIVATE',
                module: 'Field Personnel Accounts',
                record: $account,
                oldValues: [
                    'account_active' => true,
                ],
                newValues: [
                    'account_active' => false,
                ],
                description: 'Deactivated a Field Personnel account. The account can no longer access Field Collection.'
            );
        });

        return redirect()
            ->route('attendance.personnel-accounts.index')
            ->with('success', 'Field Personnel account deactivated successfully.');
    }

    private function generatePasscode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function authorizeSecretary(): void
{
    $user = request()->user();

    abort_unless(
        $user !== null
        && strtolower((string) $user->role) === 'secretary',
        403,
        'Only the Barangay Secretary is authorized to manage Field Personnel accounts.'
    );
}
}
