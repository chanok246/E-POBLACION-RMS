<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserManagementController extends Controller
{
    private const AUDIT_MODULE = 'User Management';

    private const SETUP_USER_SESSION = 'secretary_setup_user_id';

    private const SETUP_TOKEN_SESSION = 'secretary_setup_token';

    private const SETUP_EMAIL_VERIFIED_SESSION =
        'secretary_setup_email_verified_user_id';

    private const RECOVERY_USER_SESSION = 'secretary_recovery_user_id';

    private const RECOVERY_TOKEN_SESSION = 'secretary_recovery_token';

    private const EMAIL_VERIFICATION_TTL_MINUTES = 10;

    private const EMAIL_VERIFICATION_MAX_SENDS = 3;

    private const EMAIL_VERIFICATION_MAX_ATTEMPTS = 5;

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
        return view('users.create');
    }

    /**
     * Create a Secretary account and send an email verification code.
     *
     * The Secretary must verify ownership of the supplied email address
     * before being allowed to set the initial permanent password.
     */
    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'role' => ['prohibited'],
            'status' => [
                'nullable',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                ]),
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'role.prohibited' =>
                'The role cannot be chosen. This form creates Secretary accounts only.',
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['username'] = trim($validated['username']);
        $validated['email'] = mb_strtolower(
            trim($validated['email'])
        );

        $status = $validated['status'] ?? User::STATUS_ACTIVE;

        if ($status !== User::STATUS_ACTIVE) {
            return back()
                ->withInput()
                ->withErrors([
                    'status' =>
                        'A new Secretary account must be active to complete initial-password setup.',
                ]);
        }

        $setupToken = Str::random(64);
        $avatarPath = null;
        $user = null;

        try {
            if ($request->hasFile('photo')) {
                $avatarPath = $request->file('photo')->store(
                    'avatars',
                    'public'
                );

                if (
                    ! is_string($avatarPath)
                    || $avatarPath === ''
                ) {
                    throw new \RuntimeException(
                        'The profile photo could not be saved.'
                    );
                }
            }

            $user = DB::transaction(function () use (
                $validated,
                $avatarPath,
                $setupToken
            ): User {
                $user = User::create([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'email_verified_at' => null,
                    'role' => User::ROLE_SECRETARY,
                    'status' => User::STATUS_ACTIVE,
                    'password' => Str::random(64),
                    'avatar' => $avatarPath,
                    'document_processor' => false,
                ]);

                /*
                 * password_reset_tokens is keyed by email. updateOrInsert()
                 * replaces any leftover row for this address instead of
                 * failing with a duplicate primary key. This token
                 * authorizes the initial-password setup.
                 */
                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token' => Hash::make($setupToken),
                        'created_at' => now(),
                    ]
                );

                return $user;
            });
        } catch (Throwable $exception) {
            if ($avatarPath !== null) {
                Storage::disk('public')->delete($avatarPath);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'user' =>
                        'The Secretary account could not be created. Check the application logs and try again.',
                ]);
        }

        /*
         * Send the verification code before logging out the Captain.
         * If delivery fails, do not leave a newly created account with
         * an unusable initial-setup session.
         */
        $sendKey = $this->emailVerificationSendKey($user);

        RateLimiter::hit($sendKey, 900);

        try {
            $this->sendSecretaryEmailVerificationCode($user);
        } catch (Throwable $exception) {
            report($exception);

            Cache::forget(
                $this->emailVerificationCacheKey($user)
            );

            try {
                DB::transaction(function () use ($user): void {
                    DB::table('password_reset_tokens')
                        ->where('email', $user->email)
                        ->delete();

                    User::query()
                        ->whereKey($user->getKey())
                        ->delete();
                });
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }

            if ($avatarPath !== null) {
                Storage::disk('public')->delete($avatarPath);
            }

            return back()
                ->withInput()
                ->withErrors([
                    'email' =>
                        'The Secretary account could not be completed because the verification email could not be sent. Check the mail configuration and application logs before trying again.',
                ]);
        }

        $auditLog->log(
            action: 'Account Created',
            module: self::AUDIT_MODULE,
            record: $user,
            oldValues: null,
            newValues: [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'email_verified' => false,
                'role' => $user->role,
                'status' => $user->status,
                'photo_uploaded' => $avatarPath !== null,
                'initial_password_setup' => 'Pending email verification',
            ],
            description: sprintf(
                'Created a Secretary account for %s. Email verification and initial-password setup are pending.',
                $user->name
            ),
            request: $request
        );

        /*
         * End the Captain's authenticated session before the Secretary
         * continues privately with email verification and password setup.
         */
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $request->session()->put([
            self::SETUP_USER_SESSION => $user->getKey(),
            self::SETUP_TOKEN_SESSION => $setupToken,
        ]);

        return redirect()
            ->route('secretary-password-setup')
            ->with(
                'success',
                'The verification code was sent to the Secretary email address. Verify the email before setting the permanent password.'
            );
    }

    /**
     * Verify the email code for the pending initial Secretary setup.
     *
     * This endpoint must be routed to this method and must not require
     * a normal authenticated-user session. Authorization is based on
     * the short-lived server-side initial-setup session and token.
     */
    public function verifySecretaryEmail(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'verification_code' => [
                'required',
                'string',
                'digits:6',
            ],
        ]);

        $user = $this->getPendingSecretarySetup($request);

        if (! $user) {
            $this->clearPendingSecretarySetup($request);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email_verification' =>
                        'The initial setup session is invalid or expired. Ask the Captain to restart the setup process.',
                ]);
        }

        if ($user->email_verified_at !== null) {
            $request->session()->put(
                self::SETUP_EMAIL_VERIFIED_SESSION,
                $user->getKey()
            );

            Cache::forget(
                $this->emailVerificationCacheKey($user)
            );

            return redirect()
                ->route('secretary-password-setup')
                ->with(
                    'success',
                    'The email address is already verified. You may continue with password setup.'
                );
        }

        $userId = (string) $user->getKey();

        $attemptKey = $this->emailVerificationAttemptKey($user);
        $cacheKey = $this->emailVerificationCacheKey($user);

        if (
            RateLimiter::tooManyAttempts(
                $attemptKey,
                self::EMAIL_VERIFICATION_MAX_ATTEMPTS
            )
        ) {
            Cache::forget($cacheKey);

            $seconds = RateLimiter::availableIn($attemptKey);

            return back()->withErrors([
                'verification_code' =>
                    "Too many incorrect verification attempts. Try again in {$seconds} seconds or request a new code.",
            ]);
        }

        $challenge = Cache::get($cacheKey);

        if (
            ! is_array($challenge)
            || ! isset(
                $challenge['email'],
                $challenge['code_hash']
            )
            || ! is_string($challenge['email'])
            || ! is_string($challenge['code_hash'])
        ) {
            return back()->withErrors([
                'verification_code' =>
                    'The verification code has expired or no code is pending. Request a new code.',
            ]);
        }

        if (
            ! hash_equals(
                mb_strtolower((string) $user->email),
                mb_strtolower($challenge['email'])
            )
        ) {
            Cache::forget($cacheKey);

            return back()->withErrors([
                'verification_code' =>
                    'The pending verification no longer matches this account email. Request a new code.',
            ]);
        }

        if (
            ! Hash::check(
                $validated['verification_code'],
                $challenge['code_hash']
            )
        ) {
            RateLimiter::hit($attemptKey, 600);

            if (
                RateLimiter::tooManyAttempts(
                    $attemptKey,
                    self::EMAIL_VERIFICATION_MAX_ATTEMPTS
                )
            ) {
                Cache::forget($cacheKey);
            }

            return back()->withErrors([
                'verification_code' =>
                    'The verification code is incorrect or expired.',
            ]);
        }

        try {
            $verifiedUser = DB::transaction(function () use (
                $user,
                $challenge
            ): ?User {
                $lockedUser = User::query()
                    ->whereKey($user->getKey())
                    ->where('role', User::ROLE_SECRETARY)
                    ->where('status', User::STATUS_ACTIVE)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $lockedUser instanceof User
                    || mb_strtolower((string) $lockedUser->email)
                        !== mb_strtolower($challenge['email'])
                ) {
                    return null;
                }

                if ($lockedUser->email_verified_at === null) {
                    $lockedUser->email_verified_at = now();
                    $lockedUser->save();
                }

                return $lockedUser;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'verification_code' =>
                    'The email could not be verified at this time. Please try again.',
            ]);
        }

        if (! $verifiedUser) {
            Cache::forget($cacheKey);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email_verification' =>
                        'The Secretary account is no longer eligible for this verification. Ask the Captain to check the account.',
                ]);
        }

        Cache::forget($cacheKey);
        RateLimiter::clear($attemptKey);
        RateLimiter::clear(
            $this->emailVerificationSendKey($verifiedUser)
        );

        $request->session()->put(
            self::SETUP_EMAIL_VERIFIED_SESSION,
            $verifiedUser->getKey()
        );

        return redirect()
            ->route('secretary-password-setup')
            ->with(
                'success',
                'Your email address has been verified. You may now set your permanent password.'
            );
    }

    /**
     * Resend the initial Secretary email verification code.
     */
    public function resendSecretaryEmailVerification(
        Request $request
    ): RedirectResponse {
        $user = $this->getPendingSecretarySetup($request);

        if (! $user) {
            $this->clearPendingSecretarySetup($request);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email_verification' =>
                        'The initial setup session is invalid or expired. Ask the Captain to restart the setup process.',
                ]);
        }

        if ($user->email_verified_at !== null) {
            $request->session()->put(
                self::SETUP_EMAIL_VERIFIED_SESSION,
                $user->getKey()
            );

            return redirect()
                ->route('secretary-password-setup')
                ->with(
                    'success',
                    'The email address is already verified. You may continue with password setup.'
                );
        }

        $sendKey = $this->emailVerificationSendKey($user);

        if (
            RateLimiter::tooManyAttempts(
                $sendKey,
                self::EMAIL_VERIFICATION_MAX_SENDS
            )
        ) {
            $seconds = RateLimiter::availableIn($sendKey);

            return back()->withErrors([
                'email_verification' =>
                    "Too many verification emails have been requested. Try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($sendKey, 900);

        try {
            $this->sendSecretaryEmailVerificationCode($user);
        } catch (Throwable $exception) {
            report($exception);

            Cache::forget(
                $this->emailVerificationCacheKey($user)
            );

            return back()->withErrors([
                'email_verification' =>
                    'The verification email could not be sent. Check the mail configuration or try again later.',
            ]);
        }

        return redirect()
            ->route('secretary-password-setup')
            ->with(
                'success',
                'A new verification code has been sent. The previous code is no longer valid.'
            );
    }

    /**
     * Send and temporarily store a hashed six-digit verification code.
     *
     * The cache entry expires after the configured verification TTL.
     * Only the hash is stored; the plaintext code is sent by email.
     */
    private function sendSecretaryEmailVerificationCode(
        User $user
    ): void {
        $code = (string) random_int(100000, 999999);

        $cacheKey = $this->emailVerificationCacheKey($user);

        Cache::put(
            $cacheKey,
            [
                'email' => mb_strtolower(trim((string) $user->email)),
                'code_hash' => Hash::make($code),
            ],
            now()->addMinutes(
                self::EMAIL_VERIFICATION_TTL_MINUTES
            )
        );

        $message = implode("\n", [
            'Your e-Poblacion Secretary account email verification code is:',
            '',
            $code,
            '',
            'This code expires in 10 minutes.',
            'Only enter this code on the e-Poblacion account setup page.',
            'Do not share this code with anyone.',
            '',
            'If you did not expect this message, you may ignore it.',
        ]);

        try {
            Mail::raw($message, function ($mail) use ($user): void {
                $mail
                    ->to($user->email)
                    ->subject(
                        'e-Poblacion Secretary Account Verification'
                    );
            });
        } catch (Throwable $exception) {
            Cache::forget($cacheKey);

            throw $exception;
        }
    }

    /**
     * Cache key for a pending email verification challenge.
     */
    private function emailVerificationCacheKey(User $user): string
    {
        return 'secretary-email-verification:' . $user->getKey();
    }

    /**
     * Rate-limit key for email verification attempts.
     */
    private function emailVerificationAttemptKey(User $user): string
    {
        return 'secretary-email-verification-attempts:'
            . $user->getKey();
    }

    /**
     * Rate-limit key for sending verification emails.
     */
    private function emailVerificationSendKey(User $user): string
    {
        return 'secretary-email-verification-send:'
            . $user->getKey();
    }

    /**
     * Authorize a Secretary password reset after the Captain verifies
     * the Secretary's identity in person.
     *
     * Required route middleware:
     * auth, active, twofactor, role:captain.
     *
     * The identity check is an onsite procedure attested to by the
     * Captain. The application cannot independently verify physical ID.
     */
    public function authorizeSecretaryPasswordReset(
        Request $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'confirm_username' => [
                'required',
                'string',
                'max:50',
            ],
            'identity_verified' => [
                'required',
                'accepted',
            ],
        ]);

        if (! $user->isSecretary()) {
            abort(404);
        }

        if (
            ! is_string($validated['confirm_username'])
            || ! hash_equals(
                (string) $user->username,
                trim($validated['confirm_username'])
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'password_reset' =>
                        'The confirmed username does not match the selected Secretary account.',
                ]);
        }

        $recoveryToken = Str::random(64);

        try {
            $target = DB::transaction(function () use (
                $user,
                $recoveryToken,
                $auditLog,
                $request
            ): ?User {
                $account = User::query()
                    ->whereKey($user->getKey())
                    ->where('role', User::ROLE_SECRETARY)
                    ->where('status', User::STATUS_ACTIVE)
                    ->lockForUpdate()
                    ->first();

                if (! $account) {
                    return null;
                }

                /*
                 * Preserve any UNEXPIRED setup/reset token.
                 * The password_reset_tokens table is keyed by email,
                 * so two live authorizations cannot safely coexist.
                 * An expired token can no longer be used, so it is
                 * replaced instead of blocking recovery forever.
                 */
                $existingToken = DB::table('password_reset_tokens')
                    ->where('email', $account->email)
                    ->lockForUpdate()
                    ->first();

                if (
                    $existingToken
                    && $this->passwordTokenIsStillValid(
                        $existingToken->created_at
                    )
                ) {
                    return null;
                }

                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $account->email],
                    [
                        'token' => Hash::make($recoveryToken),
                        'created_at' => now(),
                    ]
                );

                $auditLog->log(
                    action: 'Password Reset Authorized',
                    module: self::AUDIT_MODULE,
                    record: $account,
                    oldValues: null,
                    newValues: [
                        'password_reset_authorized' => true,
                        'authorization_method' =>
                            'Onsite identity verification',
                        'authorized_by_role' => User::ROLE_CAPTAIN,
                    ],
                    description: sprintf(
                        'The Captain authorized an onsite password reset for Secretary account %s after attesting to an in-person identity check. No password or plaintext recovery token was recorded.',
                        $account->username
                    ),
                    request: $request
                );

                return $account;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'password_reset' =>
                    'The password reset could not be authorized. No existing setup token was intentionally replaced. Check the application logs before retrying.',
            ]);
        }

        if (! $target) {
            return back()->withErrors([
                'password_reset' =>
                    'This account is unavailable for recovery, or an unexpired setup/reset token already exists. The existing token was preserved. Check the account setup status before trying again.',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $request->session()->put([
            self::RECOVERY_USER_SESSION => $target->getKey(),
            self::RECOVERY_TOKEN_SESSION => $recoveryToken,
        ]);

        return redirect()->route('secretary-password-setup');
    }

    /**
     * Authorize a one-time, onsite Secretary authenticator recovery.
     *
     * The Captain verifies the Secretary's identity, then the Captain's
     * session ends before the Secretary can continue privately.
     * No authenticator secret or recovery code is shown to the Captain.
     */
    public function authorizeSecretaryAuthenticatorRecovery(
        Request $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        $captain = Auth::user();

        abort_unless(
            $captain instanceof User && $captain->isCaptain(),
            403
        );

        $validated = $request->validate([
            'confirm_username' => [
                'required',
                'string',
                'max:255',
            ],
            'identity_verified' => [
                'required',
                'accepted',
            ],
        ]);

        if (
            ! $user->isSecretary()
            || ! $user->isActive()
            || (int) $user->getKey() === (int) Auth::id()
            || ! hash_equals(
                (string) $user->username,
                (string) $validated['confirm_username']
            )
        ) {
            return back()
                ->withErrors([
                    'authenticator_recovery' =>
                        'The selected account is not an eligible active Secretary account, or the username does not match.',
                ])
                ->withInput();
        }

        $target = DB::transaction(function () use ($user) {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $lockedUser
                || ! $lockedUser->isSecretary()
                || ! $lockedUser->isActive()
            ) {
                return null;
            }

            return $lockedUser;
        });

        if (! $target) {
            return back()->withErrors([
                'authenticator_recovery' =>
                    'The Secretary account is no longer eligible for authenticator recovery.',
            ]);
        }

        $authorizedAt = now();
        $expiresAt = $authorizedAt->copy()->addMinutes(15);
        $authorizationToken = Str::random(64);

        $auditLog->logAction(
            'Authenticator Recovery Authorized',
            self::AUDIT_MODULE,
            (int) $target->getKey(),
            (string) $target->username,
            null,
            [
                'authorization_expires_at' =>
                    $expiresAt->toIso8601String(),
                'identity_verified_onsite' => true,
            ],
            'Captain authorized onsite Secretary authenticator recovery after identity verification.',
            $request
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $request->session()->put([
            'secretary_authenticator_recovery_user_id' =>
                $target->getKey(),
            'secretary_authenticator_recovery_token' =>
                $authorizationToken,
            'secretary_authenticator_recovery_authorized_at' =>
                $authorizedAt->timestamp,
            'secretary_authenticator_recovery_expires_at' =>
                $expiresAt->timestamp,
        ]);

        return redirect()->route(
            'secretary-authenticator-recovery.show'
        );
    }

    /**
     * Display the initial-password setup or authorized recovery form.
     */
    public function showInitialPasswordSetup(
        Request $request
    ): View|RedirectResponse {
        if ($this->hasSecretaryRecoverySession($request)) {
            $user = $this->getPendingSecretaryRecovery($request);

            if (! $user) {
                $this->clearPendingSecretaryRecovery($request);

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'setup' =>
                            'The authorized password-recovery session is missing, invalid, expired, or already used. Ask the Captain to authorize a new reset.',
                    ]);
            }

            return view('auth.secretary-password-setup', [
                'secretaryName' => $user->name,
                'email' => $user->email,
                'isPasswordRecovery' => true,
                'emailVerified' => $user->email_verified_at !== null,
            ]);
        }

        $user = $this->getPendingSecretarySetup($request);

        if (! $user) {
            $this->clearPendingSecretarySetup($request);

            return redirect()
                ->route('login')
                ->withErrors([
                    'setup' =>
                        'There is no valid onsite password-setup session. Ask the Captain to restart the setup process.',
                ]);
        }

        $emailVerified =
            $user->email_verified_at !== null
            && (string) $request->session()->get(
                self::SETUP_EMAIL_VERIFIED_SESSION
            ) === (string) $user->getKey();

        return view('auth.secretary-password-setup', [
            'secretaryName' => $user->name,
            'email' => $user->email,
            'isPasswordRecovery' => false,
            'emailVerified' => $emailVerified,
        ]);
    }

    /**
     * Save the Secretary's chosen password for initial setup or recovery.
     *
     * Initial setup requires verified email ownership. The existing
     * Captain-authorized onsite recovery flow remains separate.
     */
    public function setInitialPassword(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:12',
                'max:255',
                'confirmed',
            ],
        ]);

        $isRecovery = $this->hasSecretaryRecoverySession($request);

        if ($isRecovery) {
            $setupUserId = $request->session()->get(
                self::RECOVERY_USER_SESSION
            );

            $setupToken = $request->session()->get(
                self::RECOVERY_TOKEN_SESSION
            );
        } else {
            $setupUserId = $request->session()->get(
                self::SETUP_USER_SESSION
            );

            $setupToken = $request->session()->get(
                self::SETUP_TOKEN_SESSION
            );
        }

        if (
            ! is_numeric($setupUserId)
            || ! is_string($setupToken)
            || $setupToken === ''
        ) {
            if ($isRecovery) {
                $this->clearPendingSecretaryRecovery($request);
            } else {
                $this->clearPendingSecretarySetup($request);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'setup' =>
                        'The onsite password authorization is missing or invalid. Ask the Captain to authorize the process again.',
                ]);
        }

        $emailVerifiedUserId = $request->session()->get(
            self::SETUP_EMAIL_VERIFIED_SESSION
        );

        if (
            ! $isRecovery
            && (string) $emailVerifiedUserId
                !== (string) $setupUserId
        ) {
            return back()->withErrors([
                'email_verification' =>
                    'Verify the Secretary email address before setting the permanent password.',
            ]);
        }

        try {
            $user = DB::transaction(function () use (
                $setupUserId,
                $setupToken,
                $validated,
                $isRecovery,
                $emailVerifiedUserId,
                $auditLog,
                $request
            ): ?User {
                $expiryMinutes =
                    $this->passwordAuthorizationExpiryMinutes();

                $account = User::query()
                    ->whereKey($setupUserId)
                    ->where('role', User::ROLE_SECRETARY)
                    ->where('status', User::STATUS_ACTIVE)
                    ->lockForUpdate()
                    ->first();

                if (! $account) {
                    return null;
                }

                if (
                    ! $isRecovery
                    && (
                        $account->email_verified_at === null
                        || (string) $emailVerifiedUserId
                            !== (string) $account->getKey()
                    )
                ) {
                    return null;
                }

                $tokenRecord = DB::table('password_reset_tokens')
                    ->where('email', $account->email)
                    ->where(
                        'created_at',
                        '>=',
                        now()->subMinutes($expiryMinutes)
                    )
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $tokenRecord
                    || ! is_string($tokenRecord->token)
                    || ! Hash::check(
                        $setupToken,
                        $tokenRecord->token
                    )
                ) {
                    return null;
                }

                $account->password = $validated['password'];
                $account->save();

                DB::table('password_reset_tokens')
                    ->where('email', $account->email)
                    ->delete();

                if ($isRecovery) {
                    $auditLog->log(
                        action: 'Password Reset Completed',
                        module: self::AUDIT_MODULE,
                        record: $account,
                        oldValues: null,
                        newValues: [
                            'password_reset_completed' => true,
                            'password_value_recorded' => false,
                            'two_factor_settings_changed' => false,
                        ],
                        description: sprintf(
                            'An authorized onsite password reset was completed for Secretary account %s. The account was not automatically authenticated and existing 2FA settings were preserved.',
                            $account->username
                        ),
                        request: $request
                    );
                }

                return $account;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'setup' =>
                    'The password could not be saved. If the authorization is still valid, you may try again.',
            ]);
        }

        if (! $user) {
            if ($isRecovery) {
                $this->clearPendingSecretaryRecovery($request);
            } else {
                $this->clearPendingSecretarySetup($request);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'setup' =>
                        'The password authorization is invalid, expired, already used, or no longer valid for this account. Ask the Captain to restart the appropriate process.',
                ]);
        }

        if ($isRecovery) {
            $this->clearPendingSecretaryRecovery($request);

            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Your password has been reset. Sign in using your username and new password, then complete the required two-factor authentication challenge.'
                );
        }

        $auditLog->log(
            action: 'Initial Password Set',
            module: self::AUDIT_MODULE,
            record: $user,
            oldValues: null,
            newValues: [
                'initial_password_set' => true,
                'email_verified' => true,
            ],
            description: sprintf(
                'Initial-password setup was completed for Secretary account %s after email verification. The password value was not recorded.',
                $user->username
            ),
            request: $request
        );

        $this->clearPendingSecretarySetup($request);

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Your password has been set. Sign in using your username and new password, then complete the required two-factor authentication setup or challenge.'
            );
    }

    /**
     * Return the authorized pending Secretary for initial setup.
     */
    private function getPendingSecretarySetup(
        Request $request
    ): ?User {
        $setupUserId = $request->session()->get(
            self::SETUP_USER_SESSION
        );

        $setupToken = $request->session()->get(
            self::SETUP_TOKEN_SESSION
        );

        if (
            ! is_numeric($setupUserId)
            || ! is_string($setupToken)
            || $setupToken === ''
        ) {
            return null;
        }

        $user = User::query()
            ->whereKey($setupUserId)
            ->where('role', User::ROLE_SECRETARY)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        if (! $user) {
            return null;
        }

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where(
                'created_at',
                '>=',
                now()->subMinutes(
                    $this->passwordAuthorizationExpiryMinutes()
                )
            )
            ->first();

        if (
            ! $tokenRecord
            || ! is_string($tokenRecord->token)
            || ! Hash::check($setupToken, $tokenRecord->token)
        ) {
            return null;
        }

        return $user;
    }

    /**
     * Return the authorized pending Secretary for password recovery.
     */
    private function getPendingSecretaryRecovery(
        Request $request
    ): ?User {
        $recoveryUserId = $request->session()->get(
            self::RECOVERY_USER_SESSION
        );

        $recoveryToken = $request->session()->get(
            self::RECOVERY_TOKEN_SESSION
        );

        if (
            ! is_numeric($recoveryUserId)
            || ! is_string($recoveryToken)
            || $recoveryToken === ''
        ) {
            return null;
        }

        $user = User::query()
            ->whereKey($recoveryUserId)
            ->where('role', User::ROLE_SECRETARY)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        if (! $user) {
            return null;
        }

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where(
                'created_at',
                '>=',
                now()->subMinutes(
                    $this->passwordAuthorizationExpiryMinutes()
                )
            )
            ->first();

        if (
            ! $tokenRecord
            || ! is_string($tokenRecord->token)
            || ! Hash::check($recoveryToken, $tokenRecord->token)
        ) {
            return null;
        }

        return $user;
    }

    /**
     * Determine whether any recovery-session key is present.
     *
     * Incomplete recovery state must never be treated as initial setup.
     */
    private function hasSecretaryRecoverySession(
        Request $request
    ): bool {
        return $request->session()->has(self::RECOVERY_USER_SESSION)
            || $request->session()->has(self::RECOVERY_TOKEN_SESSION);
    }

    /**
     * Whether a password_reset_tokens row is still inside its lifetime.
     */
    private function passwordTokenIsStillValid(mixed $createdAt): bool
    {
        if ($createdAt === null || $createdAt === '') {
            return false;
        }

        try {
            $created = Carbon::parse($createdAt);
        } catch (Throwable) {
            return false;
        }

        return $created->greaterThanOrEqualTo(
            now()->subMinutes(
                $this->passwordAuthorizationExpiryMinutes()
            )
        );
    }

    /**
     * Password authorization lifetime, in minutes.
     */
    private function passwordAuthorizationExpiryMinutes(): int
    {
        return max(
            1,
            (int) config('auth.passwords.users.expire', 60)
        );
    }

    /**
     * Clear the initial setup authorization from the session.
     */
    private function clearPendingSecretarySetup(
        Request $request
    ): void {
        $request->session()->forget([
            self::SETUP_USER_SESSION,
            self::SETUP_TOKEN_SESSION,
            self::SETUP_EMAIL_VERIFIED_SESSION,
        ]);
    }

    /**
     * Clear the recovery authorization from the session.
     */
    private function clearPendingSecretaryRecovery(
        Request $request
    ): void {
        $request->session()->forget([
            self::RECOVERY_USER_SESSION,
            self::RECOVERY_TOKEN_SESSION,
        ]);
    }

    /**
     * Only Secretary accounts and the Captain's own account may be
     * edited through the administrative edit screen.
     */
    private function abortUnlessManageable(User $target): void
    {
        $isSecretary = $target->isSecretary();

        $isOwnCaptain = $target->isCaptain()
            && (string) $target->getKey() === (string) Auth::id();

        abort_unless(
            $isSecretary || $isOwnCaptain,
            403,
            'This account cannot be managed from this screen.'
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
        $this->abortUnlessManageable($user);

        return view('users.edit', [
            'user' => $user,
            'isOwnCaptainProfile' => $user->isCaptain(),
            'canChangeStatus' => $user->isSecretary(),
        ]);
    }

    /**
     * Update account details.
     *
     * Role, password, 2FA and (for the Captain) status are intentionally
     * excluded. Only name, username, email, photo and, for Secretary
     * accounts, status can change.
     */
    public function update(
        Request $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        $this->abortUnlessManageable($user);

        $isCaptainTarget = $user->isCaptain();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'remove_photo' => ['nullable', 'boolean'],
        ];

        if (! $isCaptainTarget) {
            $rules['status'] = [
                'required',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                ]),
            ];
        }

        $validated = $request->validate($rules);

        $validated['name'] = trim($validated['name']);
        $validated['username'] = trim($validated['username']);
        $validated['email'] = mb_strtolower(
            trim($validated['email'])
        );

        if ($isCaptainTarget) {
            $validated['status'] = $user->status;
        }

        if (
            (string) $user->id === (string) Auth::id()
            && $validated['status'] === User::STATUS_INACTIVE
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'status' => 'You cannot deactivate your own account.',
                ]);
        }

        if (
            $user->isCaptain()
            && $validated['status'] === User::STATUS_INACTIVE
            && $user->status === User::STATUS_ACTIVE
        ) {
            $otherActiveCaptains = User::query()
                ->where('role', User::ROLE_CAPTAIN)
                ->where('status', User::STATUS_ACTIVE)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveCaptains === 0) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status' =>
                            'The last active Captain cannot be deactivated.',
                    ]);
            }
        }

        $oldValues = [];
        $newValues = [];

        foreach (['name', 'username', 'email', 'status'] as $field) {
            $oldValue = $user->{$field};
            $newValue = $validated[$field];

            if ($oldValue !== $newValue) {
                $oldValues[$field] = $oldValue;
                $newValues[$field] = $newValue;
            }
        }

        $emailChanged = strcasecmp(
            (string) $user->email,
            (string) $validated['email']
        ) !== 0;

        $oldAvatar = $user->avatar;
        $newAvatar = $oldAvatar;
        $storedNewAvatar = null;

        try {
            if ($request->hasFile('photo')) {
                $storedNewAvatar = $request->file('photo')->store(
                    'avatars',
                    'public'
                );

                if (
                    ! is_string($storedNewAvatar)
                    || $storedNewAvatar === ''
                ) {
                    throw new \RuntimeException(
                        'The profile photo could not be saved.'
                    );
                }

                $newAvatar = $storedNewAvatar;
            } elseif (
                $request->boolean('remove_photo')
                && ! empty($oldAvatar)
            ) {
                $newAvatar = null;
            }

            $oldEmail = (string) $user->email;

            $user->name = $validated['name'];
            $user->username = $validated['username'];
            $user->email = $validated['email'];
            $user->status = $validated['status'];
            $user->avatar = $newAvatar;

            /*
             * An email change invalidates prior proof of ownership.
             * The new address must be verified before being used for
             * email-based account recovery.
             */
            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            DB::transaction(function () use (
                $user,
                $emailChanged,
                $oldEmail
            ): void {
                $user->save();

                /*
                 * A pending setup/reset token is keyed by email. Move it
                 * to the new address so it is not orphaned under the old
                 * one (which would break the Secretary's setup session).
                 */
                if ($emailChanged && $user->isSecretary()) {
                    $pendingToken = DB::table('password_reset_tokens')
                        ->where('email', $oldEmail)
                        ->lockForUpdate()
                        ->first();

                    if ($pendingToken) {
                        DB::table('password_reset_tokens')
                            ->where('email', $oldEmail)
                            ->delete();

                        DB::table('password_reset_tokens')->updateOrInsert(
                            ['email' => $user->email],
                            [
                                'token' => $pendingToken->token,
                                'created_at' => $pendingToken->created_at,
                            ]
                        );
                    }
                }
            });

            if ($emailChanged) {
                Cache::forget($this->emailVerificationCacheKey($user));
            }
        } catch (Throwable $exception) {
            if ($storedNewAvatar !== null) {
                Storage::disk('public')->delete($storedNewAvatar);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'user' =>
                        'The user account could not be updated. Check the application logs and try again.',
                ]);
        }

        if ($newAvatar !== $oldAvatar) {
            $oldValues['photo'] = $oldAvatar
                ? 'Existing photo'
                : 'No photo';

            $newValues['photo'] = $newAvatar
                ? 'Photo uploaded or replaced'
                : 'Photo removed';

            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        if ($emailChanged) {
            $oldValues['email_verified_at'] =
                $oldValues['email_verified_at'] ?? 'Previously verified or unverified';

            $newValues['email_verified_at'] =
                'Unverified; ownership must be confirmed again';
        }

        if ($oldValues !== []) {
            $auditLog->log(
                action: 'Account Updated',
                module: self::AUDIT_MODULE,
                record: $user,
                oldValues: $oldValues,
                newValues: $newValues,
                description: sprintf(
                    'Updated account details for %s.',
                    $user->name
                ),
                request: $request
            );
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User account updated successfully.');
    }

    /**
     * Reject administrator-driven password changes.
     *
     * This safeguard is intentionally retained.
     */
    public function updatePassword(
        Request $request,
        User $user,
        AuditLogService $auditLog
    ): RedirectResponse {
        if ($user->isSecretary()) {
            return back()->withErrors([
                'password' =>
                    'The Captain cannot change or reset a Secretary password. The Secretary must set or change their own password through an authorized private process.',
            ]);
        }

        return back()->withErrors([
            'password' =>
                'Administrator-driven password changes are not permitted here.',
        ]);
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
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => User::STATUS_ACTIVE],
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
        if ((string) $user->id === (string) Auth::id()) {
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
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => User::STATUS_INACTIVE],
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