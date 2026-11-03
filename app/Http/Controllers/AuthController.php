<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class AuthController extends Controller
{
    /**
     * Display the login page.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user instanceof User) {
                Auth::logout();

                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login');
            }

            $pendingUserId = session('two_factor_pending_user_id');

            if (
                $pendingUserId !== null &&
                (string) $pendingUserId === (string) $user->getAuthIdentifier() &&
                session('two_factor_passed') !== true
            ) {
                if (! $user->canAccessSystem()) {
                    Auth::logout();

                    request()->session()->invalidate();
                    request()->session()->regenerateToken();

                    return redirect()->route('login')
                        ->withErrors([
                            'username' => 'This account is not authorized to access BIS - SMART.',
                        ]);
                }

                if (! $user->hasTwoFactorEnabled()) {
                    return redirect()->route('two-factor.setup');
                }

                return redirect()->route('two-factor.challenge');
            }

            if (
                session('two_factor_passed') === true &&
                (string) $pendingUserId === (string) $user->getAuthIdentifier() &&
                $user->canAccessSystem()
            ) {
                return redirect()->route('dashboard');
            }

            Auth::logout();

            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('login');
    }

    /**
     * Authenticate an active Captain or Secretary using username and password.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            'role' => [
                'required',
                Rule::in([
                    User::ROLE_CAPTAIN,
                    User::ROLE_SECRETARY,
                ]),
            ],
        ]);

        $username = Str::lower(trim($validated['username']));

        $identity = hash(
            'sha256',
            $username . '|' . $request->ip()
        );

        $attemptKey = 'login-attempts:' . $identity;
        $lockoutKey = 'login-lockout:' . $identity;

        $maxAttempts = 5;
        $attemptWindowSeconds = 900;
        $lockoutSeconds = 60;

        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            $seconds = RateLimiter::availableIn($lockoutKey);

            $request->session()->flash(
                'login_locked_until',
                now()->timestamp + $seconds
            );

            return back()
                ->withErrors([
                    'username' => "Login is temporarily locked. Please wait {$seconds} seconds before trying again.",
                ])
                ->onlyInput('username');
        }

        if (RateLimiter::attempts($attemptKey) >= $maxAttempts) {
            RateLimiter::clear($attemptKey);
        }

        $request->session()->forget('login_locked_until');

        if (! Auth::attempt([
            'username' => $username,
            'password' => $validated['password'],
            'role' => $validated['role'],
            'status' => User::STATUS_ACTIVE,
        ], $request->boolean('remember'))) {
            RateLimiter::hit($attemptKey, $attemptWindowSeconds);

            $attempts = RateLimiter::attempts($attemptKey);

            if ($attempts >= $maxAttempts) {
                RateLimiter::clear($attemptKey);
                RateLimiter::hit($lockoutKey, $lockoutSeconds);

                $request->session()->flash(
                    'login_locked_until',
                    now()->timestamp + $lockoutSeconds
                );

                return back()
                    ->withErrors([
                        'username' => 'You have reached the maximum of 5 failed login attempts. Please wait 60 seconds before trying again.',
                    ])
                    ->onlyInput('username');
            }

            $remainingAttempts = $maxAttempts - $attempts;

            return back()
                ->withErrors([
                    'username' => 'The username, password, role, or account status is invalid. '
                    . $remainingAttempts
                    . ' attempt(s) remaining before temporary lockout.',
                ])
                ->onlyInput('username');
        }

        RateLimiter::clear($attemptKey);
        RateLimiter::clear($lockoutKey);

        $request->session()->forget('login_locked_until');

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user instanceof User || ! $user->canAccessSystem()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors([
                    'username' => 'This account is not authorized to access BIS - SMART.',
                ]);
        }

        $request->session()->forget([
            'two_factor_passed',
            'recovery_codes',
            'two_factor_recovery_codes_pending',
            'google_link_user_id',
            'captain_recovery_verified_user_id',
            'captain_recovery_verified_hash_fingerprint',
            'captain_recovery_verified_at',
        ]);

        $request->session()->put(
            'two_factor_pending_user_id',
            $user->getAuthIdentifier()
        );

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return redirect()->route('two-factor.challenge');
    }

    /*
    |--------------------------------------------------------------------------
    | Email-code password recovery
    |--------------------------------------------------------------------------
    */

    /**
     * Display the email password-recovery form.
     */
    public function showEmailPasswordRecovery(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('settings.index');
        }

        return view('login', [
            'showEmailPasswordRecoveryForm' => true,
            'emailPasswordRecoveryStage' => session(
                'email_password_recovery_stage',
                'request'
            ),
            'emailPasswordRecoveryEmail' => session(
                'email_password_recovery_email',
                ''
            ),
        ]);
    }

    /**
     * Send a one-time password-recovery code to a verified email address.
     *
     * The response intentionally does not disclose whether the email
     * belongs to an account. Only active Captain and Secretary accounts
     * with verified email addresses are eligible.
     */
    public function sendEmailPasswordRecoveryCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));

        $emailFingerprint = hash('sha256', $email);
        $ipFingerprint = hash(
            'sha256',
            (string) $request->ip()
        );

        $rateLimitKey = 'email-password-recovery-send:'
            . $emailFingerprint
            . ':'
            . $ipFingerprint;

        $genericMessage = 'If this address belongs to an eligible account with a verified email, a recovery code will be sent. Check your inbox and spam folder.';

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return redirect()
                ->route('password-recovery.show')
                ->with('status', $genericMessage)
                ->with('email_password_recovery_stage', 'request');
        }

        RateLimiter::hit($rateLimitKey, 900);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('status', User::STATUS_ACTIVE)
            ->whereIn('role', [
                User::ROLE_CAPTAIN,
                User::ROLE_SECRETARY,
            ])
            ->whereNotNull('email_verified_at')
            ->first();

        if (! $user instanceof User) {
            return redirect()
                ->route('password-recovery.show')
                ->with('status', $genericMessage)
                ->with('email_password_recovery_stage', 'request');
        }

        $code = (string) random_int(100000, 999999);

        $cacheKey = 'email-password-recovery:'
            . $emailFingerprint;

        try {
            Mail::raw(
                "Your BIS - SMART password recovery code is: {$code}\n\n"
                . "This code expires in 10 minutes and can be used only once.\n"
                . "If you did not request a password reset, do not share this code and you may ignore this message.\n\n"
                . "Barangay Poblacion, Bani, Pangasinan",
                function ($message) use ($email): void {
                    $message
                        ->to($email)
                        ->subject('BIS - SMART Password Recovery Code');
                }
            );

            Cache::put(
                $cacheKey,
                [
                    'user_id' => $user->getKey(),
                    'email' => $email,
                    'code_hash' => Hash::make($code),
                ],
                now()->addMinutes(10)
            );
        } catch (Throwable $exception) {
            report($exception);

            Cache::forget($cacheKey);
        }

        return redirect()
            ->route('password-recovery.show')
            ->with('status', $genericMessage)
            ->with('email_password_recovery_stage', 'verify')
            ->with('email_password_recovery_email', $email);
    }

    /**
     * Verify the recovery code and set a new password.
     *
     * This method never logs the user in. After resetting the password,
     * the user must use normal local login and complete the normal TOTP flow.
     */
    public function resetPasswordWithEmailCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'verification_code' => [
                'required',
                'string',
                'regex:/^[0-9]{6}$/',
            ],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'verification_code.required' => 'Enter the six-digit recovery code.',
            'verification_code.regex' => 'The recovery code must contain exactly six digits.',
            'password.min' => 'The new password must contain at least 12 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $email = Str::lower(trim($validated['email']));
        $emailFingerprint = hash('sha256', $email);
        $cacheKey = 'email-password-recovery:'
            . $emailFingerprint;

        $rateLimitKey = 'email-password-recovery-verify:'
            . $emailFingerprint
            . ':'
            . hash('sha256', (string) $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return redirect()
                ->route('password-recovery.show')
                ->withErrors([
                    'verification_code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
                ])
                ->with('email_password_recovery_stage', 'verify')
                ->with('email_password_recovery_email', $email);
        }

        RateLimiter::hit($rateLimitKey, 900);

        $recovery = Cache::get($cacheKey);

        $validRecovery = is_array($recovery)
            && isset(
                $recovery['user_id'],
                $recovery['email'],
                $recovery['code_hash']
            )
            && is_string($recovery['email'])
            && hash_equals($email, $recovery['email'])
            && is_string($recovery['code_hash'])
            && Hash::check(
                $validated['verification_code'],
                $recovery['code_hash']
            );

        if (! $validRecovery) {
            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                Cache::forget($cacheKey);
            }

            return redirect()
                ->route('password-recovery.show')
                ->withErrors([
                    'verification_code' => 'The recovery code is invalid, expired, or no longer available. Request a new code if needed.',
                ])
                ->with('email_password_recovery_stage', 'verify')
                ->with('email_password_recovery_email', $email);
        }

        $resetSucceeded = DB::transaction(function () use (
            $validated,
            $email,
            $recovery
        ): bool {
            $user = User::query()
                ->whereKey($recovery['user_id'])
                ->whereRaw('LOWER(email) = ?', [$email])
                ->whereNotNull('email_verified_at')
                ->where('status', User::STATUS_ACTIVE)
                ->whereIn('role', [
                    User::ROLE_CAPTAIN,
                    User::ROLE_SECRETARY,
                ])
                ->lockForUpdate()
                ->first();

            if (! $user instanceof User) {
                return false;
            }

            $user->password = Hash::make($validated['password']);
            $user->setRememberToken(Str::random(60));
            $user->save();

            return true;
        });

        Cache::forget($cacheKey);
        RateLimiter::clear($rateLimitKey);

        if (! $resetSucceeded) {
            return redirect()
                ->route('password-recovery.show')
                ->withErrors([
                    'verification_code' => 'This recovery request is no longer valid. Request a new code and try again.',
                ])
                ->with('email_password_recovery_stage', 'request');
        }

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Your password has been reset. Sign in using your new password and complete two-factor authentication.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Captain password recovery
    |--------------------------------------------------------------------------
    */

    /**
     * Display the Captain recovery page.
     *
     * The Blade view must display the password form only when
     * captainRecoveryVerified is true.
     */
    public function showCaptainRecovery(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('settings.index');
        }

        $verifiedAt = session('captain_recovery_verified_at');
        $verifiedUserId = session('captain_recovery_verified_user_id');
        $fingerprint = session(
            'captain_recovery_verified_hash_fingerprint'
        );

        $verificationIsCurrent = is_numeric($verifiedAt)
            && (now()->timestamp - (int) $verifiedAt) >= 0
            && (now()->timestamp - (int) $verifiedAt) <= 600
            && is_numeric($verifiedUserId)
            && is_string($fingerprint)
            && $fingerprint !== '';

        if (! $verificationIsCurrent) {
            $this->clearCaptainRecoveryVerification(request());
        }

        return view('login', [
            'showCaptainRecoveryForm' => true,
            'captainRecoveryVerified' => $verificationIsCurrent,
        ]);
    }

    /**
     * Verify the one-time Captain recovery credential.
     *
     * The raw credential is never stored in the session.
     * The verification session expires after 10 minutes.
     */
    public function verifyCaptainRecoveryCredential(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'recovery_credential' => ['required', 'string', 'max:200'],
        ], [
            'recovery_credential.required' => 'Enter your one-time recovery credential.',
        ]);

        if (
            ! Schema::hasColumn('users', 'captain_recovery_credential_hash') ||
            ! Schema::hasColumn('users', 'captain_recovery_credential_created_at')
        ) {
            return redirect()->route('captain-recovery.show')
                ->withErrors([
                    'recovery_credential' => 'Captain password recovery is not available yet. Contact the system administrator.',
                ]);
        }

        $rateLimitKey = 'captain-recovery:' . hash(
            'sha256',
            (string) $request->ip()
        );

        $maxAttempts = 5;
        $decaySeconds = 900;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return redirect()->route('captain-recovery.show')
                ->withErrors([
                    'recovery_credential' => "Too many recovery attempts. Please wait {$seconds} seconds before trying again.",
                ]);
        }

        $captain = User::query()
            ->where('role', User::ROLE_CAPTAIN)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        $credentialHash = $captain?->captain_recovery_credential_hash;
        $credentialCreatedAt = $captain?->captain_recovery_credential_created_at;

        $credentialIsValid = $captain instanceof User
            && is_string($credentialHash)
            && $credentialHash !== ''
            && $credentialCreatedAt !== null
            && Hash::check(
                $validated['recovery_credential'],
                $credentialHash
            );

        if (! $credentialIsValid) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            $this->clearCaptainRecoveryVerification($request);

            return redirect()->route('captain-recovery.show')
                ->withErrors([
                    'recovery_credential' => 'The recovery credential is invalid or has already been used.',
                ]);
        }

        RateLimiter::clear($rateLimitKey);

        $request->session()->regenerate();

        $request->session()->put([
            'captain_recovery_verified_user_id' => $captain->getKey(),
            'captain_recovery_verified_hash_fingerprint' => hash(
                'sha256',
                $credentialHash
            ),
            'captain_recovery_verified_at' => now()->timestamp,
        ]);

        return redirect()->route('captain-recovery.show')
            ->with(
                'success',
                'Recovery credential verified. You may now create a new password.'
            );
    }

    /**
     * Reset the Captain password only after server-side verification.
     */
    public function resetCaptainPassword(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'password.min' => 'The new password must contain at least 12 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if (
            ! Schema::hasColumn('users', 'captain_recovery_credential_hash') ||
            ! Schema::hasColumn('users', 'captain_recovery_credential_created_at')
        ) {
            $this->clearCaptainRecoveryVerification($request);

            return redirect()->route('captain-recovery.show')
                ->withErrors([
                    'recovery_credential' => 'Captain password recovery is not available yet. Contact the system administrator.',
                ]);
        }

        $verifiedAt = $request->session()->get(
            'captain_recovery_verified_at'
        );

        $verifiedUserId = $request->session()->get(
            'captain_recovery_verified_user_id'
        );

        $verifiedFingerprint = $request->session()->get(
            'captain_recovery_verified_hash_fingerprint'
        );

        $verificationIsCurrent = is_numeric($verifiedAt)
            && (now()->timestamp - (int) $verifiedAt) >= 0
            && (now()->timestamp - (int) $verifiedAt) <= 600
            && is_numeric($verifiedUserId)
            && is_string($verifiedFingerprint)
            && $verifiedFingerprint !== '';

        if (! $verificationIsCurrent) {
            $this->clearCaptainRecoveryVerification($request);

            return redirect()->route('captain-recovery.show')
                ->withErrors([
                    'recovery_credential' => 'Verify your recovery credential again. The verification session is missing or has expired.',
                ]);
        }

        $resetSucceeded = DB::transaction(function () use (
            $validated,
            $request,
            $auditLog,
            $verifiedUserId,
            $verifiedFingerprint
        ): bool {
            $captain = User::query()
                ->whereKey($verifiedUserId)
                ->where('role', User::ROLE_CAPTAIN)
                ->where('status', User::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $captain instanceof User) {
                return false;
            }

            $credentialHash = $captain->captain_recovery_credential_hash;
            $credentialCreatedAt = $captain->captain_recovery_credential_created_at;

            if (
                ! is_string($credentialHash) ||
                $credentialHash === '' ||
                ! $credentialCreatedAt ||
                ! hash_equals(
                    $verifiedFingerprint,
                    hash('sha256', $credentialHash)
                )
            ) {
                return false;
            }

            $captain->password = Hash::make($validated['password']);
            $captain->setRememberToken(Str::random(60));
            $captain->captain_recovery_credential_hash = null;
            $captain->captain_recovery_credential_created_at = null;
            $captain->save();

            $auditLog->logAction(
                'Captain Password Reset via Recovery Credential',
                'Authentication',
                (int) $captain->getKey(),
                (string) $captain->getKey(),
                null,
                [
                    'password_reset' => true,
                    'recovery_credential_invalidated' => true,
                ],
                'Captain password reset using a one-time recovery credential. The credential was invalidated.',
                $request
            );

            return true;
        });

        $this->clearCaptainRecoveryVerification($request);

        if (! $resetSucceeded) {
            return redirect()->route('captain-recovery.show')
                ->withErrors([
                    'recovery_credential' => 'The verified credential is no longer valid. Verify your recovery credential again.',
                ]);
        }

        return redirect()->route('login')
            ->with(
                'success',
                'Your password has been reset. Sign in using your new password and complete two-factor authentication.'
            );
    }

    /**
     * Clear the server-side Captain recovery verification state.
     */
    private function clearCaptainRecoveryVerification(
        Request $request
    ): void {
        $request->session()->forget([
            'captain_recovery_verified_user_id',
            'captain_recovery_verified_hash_fingerprint',
            'captain_recovery_verified_at',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Google account linking
    |--------------------------------------------------------------------------
    */

    /**
     * Disable regular Google Sign-In.
     *
     * Google may only be used through the account-linking flow initiated
     * from the authenticated user's Settings page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return redirect()->route('login')
            ->withErrors([
                'username' => 'Google Sign-In is disabled. Use your authorized username and password to access BIS - SMART.',
            ]);
    }

    /**
     * Handle Google OAuth callback exclusively for account linking.
     *
     * This callback must never authenticate a user into BIS - SMART.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $isLinking = $request->session()->has('google_link_user_id');

        if (! $isLinking) {
            if (Auth::check()) {
                return redirect()->route('settings.index')
                    ->withErrors([
                        'google' => 'Google Sign-In is disabled. To link a Google account, use the account-linking option in Settings.',
                    ]);
            }

            return redirect()->route('login')
                ->withErrors([
                    'username' => 'Google Sign-In is disabled. Use your authorized username and password to sign in.',
                ]);
        }

        $linkedSessionUserId = $request->session()->get(
            'google_link_user_id'
        );

        $authenticatedUser = $request->user();

        if (
            ! $authenticatedUser instanceof User ||
            (string) $authenticatedUser->getAuthIdentifier()
                !== (string) $linkedSessionUserId ||
            session('two_factor_passed') !== true ||
            (string) session('two_factor_pending_user_id')
                !== (string) $linkedSessionUserId
        ) {
            $request->session()->forget('google_link_user_id');

            return redirect()->route('login')
                ->withErrors([
                    'username' => 'The Google account-linking session is no longer valid. Sign in again, complete 2FA, and retry linking from Settings.',
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            report($exception);

            return $this->googleCallbackError(
                $request,
                'The Google account-linking session expired or could not be verified. Please try again.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->googleCallbackError(
                $request,
                'Google account linking could not be completed. Please try again.'
            );
        }

        $googleId = $googleUser->getId();
        $googleEmail = $googleUser->getEmail();
        $googleProfile = $googleUser->user ?? [];

        $verifiedValue = $googleProfile['email_verified']
            ?? $googleProfile['verified_email']
            ?? false;

        $emailVerified = filter_var(
            $verifiedValue,
            FILTER_VALIDATE_BOOLEAN
        );

        if (
            ! is_string($googleId) ||
            trim($googleId) === '' ||
            ! is_string($googleEmail) ||
            trim($googleEmail) === '' ||
            ! $emailVerified
        ) {
            return $this->googleCallbackError(
                $request,
                'Google did not provide a valid, verified account identity. Please try again with a verified Google account.'
            );
        }

        if (! Schema::hasColumn('users', 'google_id')) {
            return $this->googleCallbackError(
                $request,
                'Google identity support is not enabled in the database. Contact the system administrator.'
            );
        }

        $user = $request->user();

        if (! $user instanceof User || ! $user->canAccessSystem()) {
            $request->session()->forget('google_link_user_id');

            return redirect()->route('login')
                ->withErrors([
                    'username' => 'This local account is not authorized to link a Google account.',
                ]);
        }

        if (
            Str::lower(trim((string) $user->email)) !==
            Str::lower(trim($googleEmail))
        ) {
            return $this->googleCallbackError(
                $request,
                'The verified Google email must match your current local account email before linking.'
            );
        }

        $identityAlreadyLinked = User::query()
            ->where('google_id', $googleId)
            ->where('id', '!=', $user->getKey())
            ->exists();

        if ($identityAlreadyLinked) {
            return $this->googleCallbackError(
                $request,
                'This Google account is already linked to another local account. Contact the Captain for assistance.'
            );
        }

        if ($user->getAttribute('google_id')) {
            return $this->googleCallbackError(
                $request,
                'A Google account is already linked to this local account.'
            );
        }

        $user->google_id = $googleId;
        $user->save();

        $request->session()->forget('google_link_user_id');

        return redirect()->route('settings.index')
            ->with(
                'success',
                'Your Google account has been linked successfully.'
            );
    }

    /**
     * Return an OAuth error to the correct flow without logging out
     * the authenticated user who initiated account linking.
     */
    private function googleCallbackError(
        Request $request,
        string $message
    ): RedirectResponse {
        if ($request->session()->has('google_link_user_id')) {
            return redirect()->route('settings.index')
                ->withErrors([
                    'google' => $message,
                ]);
        }

        return redirect()->route('login')
            ->withErrors([
                'username' => $message,
            ]);
    }

    /**
     * Log out the current user and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
