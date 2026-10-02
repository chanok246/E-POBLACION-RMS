<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const AUDIT_MODULE = 'User Management';

    /**
     * Display all system users.
     */
    public function index(): View
    {
        $users = User::query()
            ->orderByRaw("
                CASE
                    WHEN role = 'captain' THEN 1
                    WHEN role = 'secretary' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the create-user form.
     */
    public function create(): View
    {
        $roles = [
            User::ROLE_SECRETARY => 'Secretary',
        ];

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created system user.
     */
    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_SECRETARY,
                ]),
            ],

            'status' => [
                'nullable',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                ]),
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'] ?? User::STATUS_ACTIVE,
            'password' => $validated['password'],
            'document_processor' => false,
        ]);

        /*
         * Password is intentionally excluded from the audit log.
         */
        $auditLog->log(
            action: 'Account Created',
            module: self::AUDIT_MODULE,
            record: $user,
            oldValues: null,
            newValues: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
            description: sprintf(
                'Created a new %s account for %s.',
                ucfirst((string) $user->role),
                $user->name
            ),
            request: $request
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Secretary account created successfully.'
            );
    }

    /**
     * Display a specific user.
     */
    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the edit-user form.
     */
    public function edit(User $user): View
    {
        $roles = [
            User::ROLE_CAPTAIN => 'Captain',
            User::ROLE_SECRETARY => 'Secretary',
        ];

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update an existing system user.
     */
    public function update(
        Request $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'role' => [
                'required',
                Rule::in([
                    User::ROLE_CAPTAIN,
                    User::ROLE_SECRETARY,
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                ]),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        /*
         * Prevent self-deactivation.
         */
        if (
            $user->id === Auth::id()
            && $validated['status'] === User::STATUS_INACTIVE
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'status' => 'You cannot deactivate your own account.',
                ]);
        }

        /*
         * Prevent the current Captain from removing
         * their own Captain role.
         */
        if (
            $user->id === Auth::id()
            && $user->isCaptain()
            && $validated['role'] !== User::ROLE_CAPTAIN
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'You cannot remove your own Captain role.',
                ]);
        }

        /*
         * Prevent deactivation of the last active Captain.
         */
        if (
            $user->isCaptain()
            && $validated['status'] === User::STATUS_INACTIVE
            && $user->status === User::STATUS_ACTIVE
        ) {
            $activeCaptainCount = User::query()
                ->where('role', User::ROLE_CAPTAIN)
                ->where('status', User::STATUS_ACTIVE)
                ->where('id', '!=', $user->id)
                ->count();

            if ($activeCaptainCount === 0) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status' =>
                            'The last active Captain cannot be deactivated.',
                    ]);
            }
        }

        /*
         * A Secretary cannot be changed into Captain.
         */
        if (
            $user->isSecretary()
            && $validated['role'] === User::ROLE_CAPTAIN
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' =>
                        'A Secretary account cannot be changed to Captain.',
                ]);
        }

        /*
         * Detect normal account changes.
         */
        $changedOldValues = [];
        $changedNewValues = [];

        if ($user->name !== $validated['name']) {
            $changedOldValues['name'] = $user->name;
            $changedNewValues['name'] = $validated['name'];
        }

        if ($user->email !== $validated['email']) {
            $changedOldValues['email'] = $user->email;
            $changedNewValues['email'] = $validated['email'];
        }

        if ($user->role !== $validated['role']) {
            $changedOldValues['role'] = $user->role;
            $changedNewValues['role'] = $validated['role'];
        }

        if ($user->status !== $validated['status']) {
            $changedOldValues['status'] = $user->status;
            $changedNewValues['status'] = $validated['status'];
        }

        /*
         * Password is never written into the audit log.
         */
        $passwordChanged = ! empty($validated['password']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        /*
         * User model has:
         *
         * 'password' => 'hashed'
         *
         * Therefore Laravel hashes the password automatically.
         */
        if ($passwordChanged) {
            $user->password = $validated['password'];
        }

        $user->save();

        /*
         * Audit normal account changes.
         */
        if (! empty($changedOldValues)) {
            $auditLog->log(
                action: 'Account Updated',
                module: self::AUDIT_MODULE,
                record: $user,
                oldValues: $changedOldValues,
                newValues: $changedNewValues,
                description: sprintf(
                    'Updated account details for %s.',
                    $user->name
                ),
                request: $request
            );
        }

        /*
         * Audit password change separately.
         *
         * IMPORTANT:
         * The actual password and password hash are NEVER stored.
         */
        if ($passwordChanged) {
            $auditLog->log(
                action: 'Password Changed',
                module: self::AUDIT_MODULE,
                record: $user,
                oldValues: null,
                newValues: [
                    'password_changed' => true,
                ],
                description: sprintf(
                    'Password changed for user account %s. The password value is not stored in the audit log.',
                    $user->name
                ),
                request: $request
            );
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User account updated successfully.'
            );
    }

    /**
     * Change a user's password without changing
     * the other account details.
     */
    public function updatePassword(
        Request $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        /*
         * User model has:
         *
         * 'password' => 'hashed'
         *
         * Laravel therefore hashes the password automatically.
         */
        $user->password = $validated['password'];
        $user->save();

        /*
         * Never store the actual password or password hash.
         */
        $auditLog->log(
            action: 'Password Changed',
            module: self::AUDIT_MODULE,
            record: $user,
            oldValues: null,
            newValues: [
                'password_changed' => true,
            ],
            description: sprintf(
                'Password changed for user account %s. The password value is not stored in the audit log.',
                $user->name
            ),
            request: $request
        );

        return redirect()
            ->route('users.show', $user)
            ->with(
                'success',
                'User password updated successfully.'
            );
    }

    /**
     * Activate a user account.
     */
    public function activate(
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        if ($user->status === User::STATUS_ACTIVE) {
            return back()->with(
                'info',
                'This user account is already active.'
            );
        }

        $oldStatus = $user->status;

        $user->status = User::STATUS_ACTIVE;
        $user->save();

        $auditLog->log(
            action: 'Account Activated',
            module: self::AUDIT_MODULE,
            record: $user,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => User::STATUS_ACTIVE,
            ],
            description: sprintf(
                'Activated the user account for %s.',
                $user->name
            )
        );

        return back()->with(
            'success',
            'User account activated successfully.'
        );
    }

    /**
     * Deactivate a user account.
     */
    public function deactivate(
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        /*
         * A user cannot deactivate their own account.
         */
        if ($user->id === Auth::id()) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own account.',
            ]);
        }

        if ($user->status === User::STATUS_INACTIVE) {
            return back()->with(
                'info',
                'This user account is already inactive.'
            );
        }

        /*
         * Never allow the last active Captain
         * to be deactivated.
         */
        if ($user->isCaptain()) {
            $activeCaptainCount = User::query()
                ->where('role', User::ROLE_CAPTAIN)
                ->where('status', User::STATUS_ACTIVE)
                ->count();

            if ($activeCaptainCount <= 1) {
                return back()->withErrors([
                    'user' =>
                        'The last active Captain cannot be deactivated.',
                ]);
            }
        }

        $oldStatus = $user->status;

        $user->status = User::STATUS_INACTIVE;
        $user->save();

        $auditLog->log(
            action: 'Account Deactivated',
            module: self::AUDIT_MODULE,
            record: $user,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => User::STATUS_INACTIVE,
            ],
            description: sprintf(
                'Deactivated the user account for %s.',
                $user->name
            )
        );

        return back()->with(
            'success',
            'User account deactivated successfully.'
        );
    }
}