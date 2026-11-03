<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class TwoFactorController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 300;
    private const RECOVERY_CODE_COUNT = 8;
    private const TOTP_WINDOW = 1;
    private const TOTP_REPLAY_SECONDS = 120;

    private const EMAIL_RECOVERY_CODE_TTL_SECONDS = 600;
    private const EMAIL_RECOVERY_SEND_LIMIT = 3;
    private const EMAIL_RECOVERY_SEND_DECAY_SECONDS = 900;
    private const EMAIL_RECOVERY_REPLACEMENT_TTL_SECONDS = 900;

    private const EMAIL_RECOVERY_SESSION_KEYS = [
        'two_factor_email_recovery_user_id',
        'two_factor_email_recovery_code_hash',
        'two_factor_email_recovery_expires_at',
        'two_factor_email_recovery_password_hash',
        'two_factor_email_recovery_email',
    ];

    /*
     * This authorization is created only after successful email recovery.
     * It is separate from the ordinary two-factor challenge result.
     */
    private const EMAIL_REPLACEMENT_AUTH_SESSION_KEYS = [
        'two_factor_email_replacement_user_id',
        'two_factor_email_replacement_password_hash',
        'two_factor_email_replacement_expires_at',
    ];

    private const SECRETARY_RECOVERY_SESSION_KEYS = [
        'secretary_authenticator_recovery_user_id',
        'secretary_authenticator_recovery_token',
        'secretary_authenticator_recovery_authorized_at',
        'secretary_authenticator_recovery_expires_at',
        'secretary_authenticator_recovery_stage',
        'secretary_authenticator_recovery_password_hash',
        'secretary_authenticator_recovery_old_secret_hash',
        'secretary_authenticator_recovery_new_secret',
        'secretary_authenticator_recovery_new_codes',
    ];

    private const REPLACEMENT_SESSION_KEYS = [
        'authenticator_replacement_user_id',
        'authenticator_replacement_old_secret_hash',
        'authenticator_replacement_new_secret',
        'authenticator_replacement_stage',
        'authenticator_replacement_recovery_only',
        'authenticator_replacement_recovery_expires_at',
        'authenticator_replacement_recovery_password_hash',
    ];

    private function pendingUser(Request $request): ?User
    {
        $user = $request->user();
        $pendingId = $request->session()->get('two_factor_pending_user_id');

        if (
            ! $user instanceof User
            || $pendingId === null
            || (string) $pendingId !== (string) $user->getAuthIdentifier()
            || ! $user->isActive()
            || ! $user->hasValidSystemRole()
            || ! $user->canAccessSystem()
        ) {
            return null;
        }

        return $user;
    }

    /**
     * Return the authenticated user when either:
     * - the ordinary two-factor challenge has been completed;
     * - email recovery has authorized a restricted replacement flow; or
     * - a previously authorized recovery-only replacement is in progress.
     *
     * Email recovery alone must never set two_factor_passed to true.
     */
    private function replacementUser(Request $request): ?User
    {
        $user = $request->user();

        if (
            ! $user instanceof User
            || ! $user->isActive()
            || ! $user->hasValidSystemRole()
            || ! $user->canAccessSystem()
            || ! $user->hasTwoFactorEnabled()
            || empty($user->two_factor_secret)
            || (string) $request->session()->get(
                'two_factor_pending_user_id'
            ) !== (string) $user->getAuthIdentifier()
        ) {
            return null;
        }

        $replacementUserId = $request->session()->get(
            'authenticator_replacement_user_id'
        );

        if (
            $replacementUserId !== null
            && (string) $replacementUserId
                !== (string) $user->getAuthIdentifier()
        ) {
            return null;
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return $user;
        }

        /*
         * A recovery-only replacement remains restricted to this controller
         * and the replacement page. It is not a completed login.
         */
        if (
            $request->session()->get(
                'authenticator_replacement_recovery_only'
            ) === true
        ) {
            $passwordHash = $request->session()->get(
                'authenticator_replacement_recovery_password_hash'
            );
            $expiresAt = $request->session()->get(
                'authenticator_replacement_recovery_expires_at'
            );

            if (
                is_string($passwordHash)
                && $passwordHash !== ''
                && hash_equals($passwordHash, (string) $user->password)
                && is_numeric($expiresAt)
                && (int) $expiresAt > now()->timestamp
            ) {
                return $user;
            }

            $this->clearAuthenticatorReplacement($request);

            return null;
        }

        if ($this->hasEmailReplacementAuthorization($request, $user)) {
            return $user;
        }

        return null;
    }

    private function restartLogin(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'username' => 'Please sign in again to verify your account.',
        ]);
    }

    private function setupRateLimitKey(User $user): string
    {
        return 'two-factor-setup:' . $user->getAuthIdentifier();
    }

    private function challengeRateLimitKey(User $user): string
    {
        return 'two-factor:' . $user->getAuthIdentifier();
    }

    private function replacementRateLimitKey(User $user): string
    {
        return 'two-factor-replacement:' . $user->getAuthIdentifier();
    }

    private function emailRecoverySendRateLimitKey(User $user): string
    {
        return 'two-factor-email-recovery-send:' .
            $user->getAuthIdentifier();
    }

    private function emailRecoveryVerifyRateLimitKey(User $user): string
    {
        return 'two-factor-email-recovery-verify:' .
            $user->getAuthIdentifier();
    }

    private function sensitiveView(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, private'
            )
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function claimTotpCode(User $user, string $code): bool
    {
        $key = 'two-factor-used:' . hash(
            'sha256',
            $user->getAuthIdentifier() . '|' . $code
        );

        return Cache::add($key, true, self::TOTP_REPLAY_SECONDS);
    }

    private function recoveryCodesPending(Request $request): bool
    {
        return $request->session()->get(
            'two_factor_recovery_codes_pending'
        ) === true;
    }

    private function sessionRecoveryCodes(Request $request): ?array
    {
        $codes = $request->session()->get('recovery_codes');

        if (
            ! is_array($codes)
            || count($codes) !== self::RECOVERY_CODE_COUNT
        ) {
            return null;
        }

        foreach ($codes as $code) {
            if (! is_string($code) || $code === '') {
                return null;
            }
        }

        return array_values($codes);
    }

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::random(10))
            ->all();
    }

    private function clearAuthenticatorReplacement(Request $request): void
    {
        $request->session()->forget(self::REPLACEMENT_SESSION_KEYS);
    }

    private function clearEmailRecovery(Request $request): void
    {
        $request->session()->forget(self::EMAIL_RECOVERY_SESSION_KEYS);
    }

    private function clearEmailReplacementAuthorization(
        Request $request
    ): void {
        $request->session()->forget(
            self::EMAIL_REPLACEMENT_AUTH_SESSION_KEYS
        );
    }

    /**
     * Validate the short-lived authorization created only by successful
     * email recovery. This authorization is not a completed 2FA challenge.
     */
    private function hasEmailReplacementAuthorization(
        Request $request,
        User $user
    ): bool {
        $userId = $request->session()->get(
            'two_factor_email_replacement_user_id'
        );
        $passwordHash = $request->session()->get(
            'two_factor_email_replacement_password_hash'
        );
        $expiresAt = $request->session()->get(
            'two_factor_email_replacement_expires_at'
        );

        $valid =
            (string) $userId === (string) $user->getAuthIdentifier()
            && is_string($passwordHash)
            && $passwordHash !== ''
            && hash_equals($passwordHash, (string) $user->password)
            && is_numeric($expiresAt)
            && (int) $expiresAt > now()->timestamp
            && $request->session()->get('two_factor_passed') !== true
            && (string) $request->session()->get(
                'two_factor_pending_user_id'
            ) === (string) $user->getAuthIdentifier();

        if (! $valid) {
            $this->clearEmailReplacementAuthorization($request);
        }

        return $valid;
    }

    private function replacementStateIsValid(
        Request $request,
        User $user,
        string $expectedStage
    ): bool {
        $newSecret = $request->session()->get(
            'authenticator_replacement_new_secret'
        );
        $oldSecretHash = $request->session()->get(
            'authenticator_replacement_old_secret_hash'
        );

        return
            (string) $request->session()->get(
                'authenticator_replacement_user_id'
            ) === (string) $user->getAuthIdentifier()
            && $request->session()->get(
                'authenticator_replacement_stage'
            ) === $expectedStage
            && is_string($newSecret)
            && $newSecret !== ''
            && is_string($oldSecretHash)
            && $oldSecretHash !== ''
            && is_string($user->two_factor_secret)
            && hash_equals(
                $oldSecretHash,
                hash('sha256', $user->two_factor_secret)
            );
    }

    /**
     * Display the restricted authenticator-replacement page.
     *
     * This page is available to a fully verified user replacing an
     * authenticator, or to a user holding a valid email-recovery
     * replacement authorization. The latter is not granted normal access.
     */
    public function showAuthenticatorRecovery(
        Request $request
    ): Response|RedirectResponse {
        $user = $this->replacementUser($request);

        if (! $user) {
            $this->clearAuthenticatorReplacement($request);
            $this->clearEmailReplacementAuthorization($request);

            return $this->restartLogin($request);
        }

        $stage = $request->session()->get(
            'authenticator_replacement_stage',
            'start'
        );

        if (! in_array(
            $stage,
            ['start', 'verify_new'],
            true
        )) {
            $this->clearAuthenticatorReplacement($request);

            return redirect()
                ->route('settings.authenticator-replacement.page')
                ->withErrors([
                    'new_code' => 'The replacement session is invalid. Please start the replacement process again.',
                ]);
        }

        $data = [
            'user' => $user,
            'replacementStage' => $stage,
            'replacementQrCodeSvg' => null,
            'replacementSecretKey' => null,
            'replacementRecoveryCodes' => [],
        ];

        if ($stage === 'verify_new') {
            if (! $this->replacementStateIsValid(
                $request,
                $user,
                'verify_new'
            )) {
                $recoveryOnly = $request->session()->get(
                    'authenticator_replacement_recovery_only'
                ) === true;

                $this->clearAuthenticatorReplacement($request);

                if ($recoveryOnly) {
                    return $this->restartLogin($request);
                }

                return redirect()
                    ->route('settings.authenticator-replacement.page')
                    ->withErrors([
                        'new_code' => 'The replacement session expired or the active authenticator changed. No replacement was made. Start again.',
                    ]);
            }

            $secret = $request->session()->get(
                'authenticator_replacement_new_secret'
            );

            $qrCodeUrl = app(Google2FA::class)->getQRCodeUrl(
                config('app.name', 'e-Poblacion'),
                $user->username ?: $user->email,
                $secret
            );

            $data['replacementQrCodeSvg'] = QrCode::size(220)
                ->margin(1)
                ->generate($qrCodeUrl)
                ->toHtml();

            $data['replacementSecretKey'] = $secret;
        }


        return $this->sensitiveView(
            'auth.authenticator-recovery',
            $data
        );
    }

    /**
     * Start authenticator replacement.
     *
     * Normal path: current password + old authenticator code.
     * Lost-phone path: current password + valid, unused authorization
     * created by successful email recovery.
     *
     * The active secret and recovery codes are not changed here.
     */
    public function startAuthenticatorReplacement(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:255'],
            'old_code' => ['nullable', 'digits:6'],
        ]);

        $user = $this->replacementUser($request);

        if (! $user) {
            $this->clearAuthenticatorReplacement($request);
            $this->clearEmailReplacementAuthorization($request);

            return $this->restartLogin($request);
        }

        $key = $this->replacementRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'old_code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        if (! Hash::check($validated['current_password'], $user->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        if (empty($user->two_factor_secret)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'old_code' => 'Your existing authenticator is not available. No changes were made.',
            ]);
        }

        $oldCode = $validated['old_code'] ?? null;
        $usingEmailRecovery = false;

        if (is_string($oldCode) && $oldCode !== '') {
            $google2fa = app(Google2FA::class);

            if (! $google2fa->verifyKey(
                $user->two_factor_secret,
                $oldCode,
                self::TOTP_WINDOW
            )) {
                RateLimiter::hit($key, self::DECAY_SECONDS);

                return back()->withErrors([
                    'old_code' => 'The code from your OLD phone is invalid.',
                ]);
            }

            if (! $this->claimTotpCode($user, $oldCode)) {
                RateLimiter::hit($key, self::DECAY_SECONDS);

                return back()->withErrors([
                    'old_code' => 'That code has already been used. Wait for the next code from your old phone.',
                ]);
            }
        } else {
            if (! $this->hasEmailReplacementAuthorization($request, $user)) {
                RateLimiter::hit($key, self::DECAY_SECONDS);

                return back()->withErrors([
                    'old_code' => 'Enter the code from your old phone, or complete email recovery from the two-factor challenge before replacing a lost authenticator.',
                ]);
            }

            $usingEmailRecovery = true;
        }

        RateLimiter::clear($key);

        $oldSecret = (string) $user->two_factor_secret;
        $newSecret = app(Google2FA::class)->generateSecretKey();

        /*
         * Preserve the email-recovery expiry and password binding before
         * consuming the one-time authorization.
         */
        $recoveryExpiresAt = null;
        $recoveryPasswordHash = null;

        if ($usingEmailRecovery) {
            $recoveryExpiresAt = $request->session()->get(
                'two_factor_email_replacement_expires_at'
            );
            $recoveryPasswordHash = $request->session()->get(
                'two_factor_email_replacement_password_hash'
            );
        }

        $this->clearAuthenticatorReplacement($request);

        if ($usingEmailRecovery) {
            $this->clearEmailReplacementAuthorization($request);
        }

        $replacementSession = [
            'authenticator_replacement_user_id' =>
                $user->getAuthIdentifier(),
            'authenticator_replacement_old_secret_hash' =>
                hash('sha256', $oldSecret),
            'authenticator_replacement_new_secret' => $newSecret,
            'authenticator_replacement_stage' => 'verify_new',
            'authenticator_replacement_recovery_only' => $usingEmailRecovery,
        ];

        if ($usingEmailRecovery) {
            $replacementSession[
                'authenticator_replacement_recovery_expires_at'
            ] = $recoveryExpiresAt;

            $replacementSession[
                'authenticator_replacement_recovery_password_hash'
            ] = $recoveryPasswordHash;
        }

        $request->session()->put($replacementSession);

        $message = $usingEmailRecovery
            ? 'Email recovery was verified. Scan the temporary QR code with your new phone and verify its code. Your old authenticator remains active until you finish the process.'
            : 'The old authenticator is still active. Scan the temporary QR code with your new phone, then verify its code.';

        return redirect()
            ->route('settings.authenticator-replacement.page')
            ->with('success', $message);
    }

    public function verifyAuthenticatorReplacement(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'new_code' => ['required', 'digits:6'],
        ]);

        $user = $this->replacementUser($request);

        if (! $user) {
            $this->clearAuthenticatorReplacement($request);

            return $this->restartLogin($request);
        }

        if (! $this->replacementStateIsValid(
            $request,
            $user,
            'verify_new'
        )) {
            $recoveryOnly = $request->session()->get(
                'authenticator_replacement_recovery_only'
            ) === true;

            $this->clearAuthenticatorReplacement($request);

            if ($recoveryOnly) {
                return $this->restartLogin($request);
            }

            return redirect()
                ->route('settings.authenticator-replacement.page')
                ->withErrors([
                    'new_code' => 'The replacement session expired or the active authenticator changed. No replacement was made. Start again.',
                ]);
        }

        $key = $this->replacementRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'new_code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        $newSecret = $request->session()->get(
            'authenticator_replacement_new_secret'
        );

        if (! app(Google2FA::class)->verifyKey(
            $newSecret,
            $validated['new_code'],
            self::TOTP_WINDOW
        )) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'new_code' => 'The code from your NEW phone is invalid. Your old authenticator remains active.',
            ]);
        }

        if (! $this->claimTotpCode($user, $validated['new_code'])) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'new_code' => 'That code has already been used. Wait for the next code from your new phone.',
            ]);
        }

        RateLimiter::clear($key);

        $oldSecretHash = $request->session()->get(
            'authenticator_replacement_old_secret_hash'
        );
        $recoveryOnly = $request->session()->get(
            'authenticator_replacement_recovery_only'
        ) === true;
        $recoveryPasswordHash = $request->session()->get(
            'authenticator_replacement_recovery_password_hash'
        );
        $recoveryExpiresAt = $request->session()->get(
            'authenticator_replacement_recovery_expires_at'
        );

        if (
            $recoveryOnly
            && (
                ! is_string($recoveryPasswordHash)
                || ! hash_equals(
                    $recoveryPasswordHash,
                    (string) $user->password
                )
                || ! is_numeric($recoveryExpiresAt)
                || (int) $recoveryExpiresAt <= now()->timestamp
            )
        ) {
            $this->clearAuthenticatorReplacement($request);

            return $this->restartLogin($request);
        }

        $committed = DB::transaction(function () use (
            $user,
            $oldSecretHash,
            $newSecret,
            $recoveryOnly,
            $recoveryPasswordHash
        ): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $lockedUser
                || ! $lockedUser->isActive()
                || ! $lockedUser->hasValidSystemRole()
                || ! $lockedUser->canAccessSystem()
                || ! $lockedUser->hasTwoFactorEnabled()
                || ! is_string($lockedUser->two_factor_secret)
                || ! is_string($oldSecretHash)
                || ! hash_equals(
                    $oldSecretHash,
                    hash('sha256', $lockedUser->two_factor_secret)
                )
                || (
                    $recoveryOnly
                    && (
                        ! is_string($recoveryPasswordHash)
                        || ! hash_equals(
                            $recoveryPasswordHash,
                            (string) $lockedUser->password
                        )
                    )
                )
            ) {
                return false;
            }

            $lockedUser->two_factor_secret = $newSecret;
            $lockedUser->save();

            return true;
        });

        if (! $committed) {
            $this->clearAuthenticatorReplacement($request);

            if ($recoveryOnly) {
                return $this->restartLogin($request);
            }

            return redirect()
                ->route('settings.authenticator-replacement.page')
                ->withErrors([
                    'new_code' => 'Your account changed during replacement. The new authenticator was not activated. Please start again.',
                ]);
        }

        $this->clearAuthenticatorReplacement($request);
        $this->clearEmailReplacementAuthorization($request);

        if ($recoveryOnly) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'success',
                'Authenticator recovery completed. Sign in again with your account password and the new authenticator.'
            );
        }

        return redirect()->route('settings.index')->with(
            'success',
            'Your new authenticator is now active. Your existing recovery codes have not been changed.'
        );
    }

    /**
     * Kept temporarily for compatibility with the existing route.
     * Replacement now activates immediately after the new TOTP code verifies.
     */
    public function finishAuthenticatorReplacement(
        Request $request
    ): RedirectResponse {
        return redirect()
            ->route('settings.authenticator-replacement.page')
            ->withErrors([
                'new_code' => 'The replacement flow has changed. Verify the code from your new authenticator to activate it.',
            ]);
    }

    private function secretaryRecoveryUser(Request $request): ?User
    {
        if (Auth::check()) {
            return null;
        }

        $userId = $request->session()->get(
            'secretary_authenticator_recovery_user_id'
        );
        $token = $request->session()->get(
            'secretary_authenticator_recovery_token'
        );
        $authorizedAt = $request->session()->get(
            'secretary_authenticator_recovery_authorized_at'
        );
        $expiresAt = $request->session()->get(
            'secretary_authenticator_recovery_expires_at'
        );

        if (
            ! is_numeric($userId)
            || ! is_string($token)
            || strlen($token) !== 64
            || ! is_numeric($authorizedAt)
            || ! is_numeric($expiresAt)
            || (int) $authorizedAt > now()->timestamp
            || (int) $expiresAt <= now()->timestamp
            || (int) $expiresAt > ((int) $authorizedAt + 15 * 60)
        ) {
            return null;
        }

        $user = User::query()->find($userId);

        if (
            ! $user
            || ! $user->isSecretary()
            || ! $user->isActive()
            || ! $user->hasValidSystemRole()
            || ! $user->hasTwoFactorEnabled()
            || empty($user->two_factor_secret)
        ) {
            return null;
        }

        return $user;
    }

    private function clearSecretaryAuthenticatorRecovery(
        Request $request
    ): void {
        $request->session()->forget(self::SECRETARY_RECOVERY_SESSION_KEYS);
    }

    private function secretaryRecoveryRateLimitKey(User $user): string
    {
        return 'secretary-authenticator-recovery:' .
            $user->getAuthIdentifier();
    }

    private function secretaryRecoveryStateIsValid(
        Request $request,
        User $user,
        string $stage
    ): bool {
        $newSecret = $request->session()->get(
            'secretary_authenticator_recovery_new_secret'
        );
        $oldSecretHash = $request->session()->get(
            'secretary_authenticator_recovery_old_secret_hash'
        );
        $passwordHash = $request->session()->get(
            'secretary_authenticator_recovery_password_hash'
        );

        return
            (string) $request->session()->get(
                'secretary_authenticator_recovery_user_id'
            ) === (string) $user->getAuthIdentifier()
            && $request->session()->get(
                'secretary_authenticator_recovery_stage'
            ) === $stage
            && is_string($newSecret)
            && $newSecret !== ''
            && is_string($oldSecretHash)
            && hash_equals(
                $oldSecretHash,
                hash('sha256', (string) $user->two_factor_secret)
            )
            && is_string($passwordHash)
            && hash_equals($passwordHash, (string) $user->password);
    }

    public function showSecretaryAuthenticatorRecovery(
        Request $request
    ): Response|RedirectResponse {
        $user = $this->secretaryRecoveryUser($request);

        if (! $user) {
            $this->clearSecretaryAuthenticatorRecovery($request);

            return redirect()->route('login')->withErrors([
                'username' => 'The authenticator recovery authorization is invalid or expired. Ask the Captain to authorize it again.',
            ]);
        }

        $stage = $request->session()->get(
            'secretary_authenticator_recovery_stage',
            'verify_credentials'
        );

        if (! in_array(
            $stage,
            ['verify_credentials', 'verify_new', 'confirm_recovery_codes'],
            true
        )) {
            $this->clearSecretaryAuthenticatorRecovery($request);

            return redirect()->route('login')->withErrors([
                'username' => 'The authenticator recovery session is invalid. Ask the Captain to authorize it again.',
            ]);
        }

        $data = [
            'stage' => $stage,
            'username' => $user->username,
        ];

        if ($stage === 'verify_new') {
            $secret = $request->session()->get(
                'secretary_authenticator_recovery_new_secret'
            );

            if (! is_string($secret) || $secret === '') {
                $this->clearSecretaryAuthenticatorRecovery($request);

                return redirect()->route('login')->withErrors([
                    'username' => 'The temporary authenticator setup expired. Ask the Captain to authorize recovery again.',
                ]);
            }

            $qrCodeUrl = app(Google2FA::class)->getQRCodeUrl(
                config('app.name', 'e-Poblacion'),
                $user->username ?: $user->email,
                $secret
            );

            $data['qrCodeSvg'] = QrCode::size(220)
                ->margin(1)
                ->generate($qrCodeUrl)
                ->toHtml();
            $data['secretKey'] = $secret;
        }

        if ($stage === 'confirm_recovery_codes') {
            $codes = $request->session()->get(
                'secretary_authenticator_recovery_new_codes'
            );

            if (
                ! is_array($codes)
                || count($codes) !== self::RECOVERY_CODE_COUNT
            ) {
                $this->clearSecretaryAuthenticatorRecovery($request);

                return redirect()->route('login')->withErrors([
                    'username' => 'The temporary recovery codes are unavailable. Ask the Captain to authorize recovery again.',
                ]);
            }

            $data['recoveryCodes'] = array_values($codes);
        }

        return $this->sensitiveView(
            'auth.secretary-authenticator-recovery',
            $data
        );
    }

    public function verifySecretaryAuthenticatorRecovery(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = $this->secretaryRecoveryUser($request);

        if (! $user) {
            $this->clearSecretaryAuthenticatorRecovery($request);

            return redirect()->route('login')->withErrors([
                'username' => 'The authenticator recovery authorization is invalid or expired. Ask the Captain to authorize it again.',
            ]);
        }

        $key = $this->secretaryRecoveryRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'password' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        if (! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'password' => 'The account password is incorrect. No account changes were made.',
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->put([
            'secretary_authenticator_recovery_stage' => 'verify_new',
            'secretary_authenticator_recovery_password_hash' =>
                (string) $user->password,
            'secretary_authenticator_recovery_old_secret_hash' =>
                hash('sha256', (string) $user->two_factor_secret),
            'secretary_authenticator_recovery_new_secret' =>
                app(Google2FA::class)->generateSecretKey(),
        ]);

        $request->session()->forget(
            'secretary_authenticator_recovery_new_codes'
        );

        return redirect()->route('secretary-authenticator-recovery.show');
    }

    public function confirmSecretaryAuthenticatorRecovery(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'new_code' => ['required', 'digits:6'],
        ]);

        $user = $this->secretaryRecoveryUser($request);

        if (
            ! $user
            || ! $this->secretaryRecoveryStateIsValid(
                $request,
                $user,
                'verify_new'
            )
        ) {
            $this->clearSecretaryAuthenticatorRecovery($request);

            return redirect()->route('login')->withErrors([
                'username' => 'The recovery session expired or the account changed. No authenticator changes were made.',
            ]);
        }

        $key = $this->secretaryRecoveryRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'new_code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        $secret = $request->session()->get(
            'secretary_authenticator_recovery_new_secret'
        );

        if (
            ! app(Google2FA::class)->verifyKey(
                $secret,
                $validated['new_code'],
                self::TOTP_WINDOW
            )
            || ! $this->claimTotpCode($user, $validated['new_code'])
        ) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'new_code' => 'The new authenticator code is invalid or already used. Wait for the next code and try again.',
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->put([
            'secretary_authenticator_recovery_new_codes' =>
                $this->generateRecoveryCodes(),
            'secretary_authenticator_recovery_stage' =>
                'confirm_recovery_codes',
        ]);

        return redirect()->route('secretary-authenticator-recovery.show');
    }

    public function finishSecretaryAuthenticatorRecovery(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $request->validate([
            'saved_confirmation' => ['required', 'accepted'],
        ]);

        $user = $this->secretaryRecoveryUser($request);
        $newSecret = $request->session()->get(
            'secretary_authenticator_recovery_new_secret'
        );
        $newCodes = $request->session()->get(
            'secretary_authenticator_recovery_new_codes'
        );

        if (
            ! $user
            || ! $this->secretaryRecoveryStateIsValid(
                $request,
                $user,
                'confirm_recovery_codes'
            )
            || ! is_string($newSecret)
            || $newSecret === ''
            || ! is_array($newCodes)
            || count($newCodes) !== self::RECOVERY_CODE_COUNT
        ) {
            $this->clearSecretaryAuthenticatorRecovery($request);

            return redirect()->route('login')->withErrors([
                'username' => 'The recovery session expired or is incomplete. No authenticator changes were made.',
            ]);
        }

        foreach ($newCodes as $code) {
            if (! is_string($code) || $code === '') {
                $this->clearSecretaryAuthenticatorRecovery($request);

                return redirect()->route('login')->withErrors([
                    'username' => 'The temporary recovery codes are invalid. No authenticator changes were made.',
                ]);
            }
        }

        $oldSecretHash = $request->session()->get(
            'secretary_authenticator_recovery_old_secret_hash'
        );
        $passwordHash = $request->session()->get(
            'secretary_authenticator_recovery_password_hash'
        );

        $committed = DB::transaction(function () use (
            $user,
            $oldSecretHash,
            $passwordHash,
            $newSecret,
            $newCodes,
            $auditLog,
            $request
        ): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $lockedUser
                || ! $lockedUser->isSecretary()
                || ! $lockedUser->isActive()
                || ! $lockedUser->hasValidSystemRole()
                || ! $lockedUser->hasTwoFactorEnabled()
                || ! is_string($lockedUser->two_factor_secret)
                || ! is_string($oldSecretHash)
                || ! hash_equals(
                    $oldSecretHash,
                    hash('sha256', $lockedUser->two_factor_secret)
                )
                || ! is_string($passwordHash)
                || ! hash_equals(
                    $passwordHash,
                    (string) $lockedUser->password
                )
            ) {
                return false;
            }

            $lockedUser->two_factor_secret = $newSecret;
            $lockedUser->two_factor_recovery_codes = json_encode(
                array_values($newCodes),
                JSON_THROW_ON_ERROR
            );
            $lockedUser->save();

            $auditLog->logAction(
                'Secretary Authenticator Recovery Completed',
                'User Management',
                (int) $lockedUser->getKey(),
                (string) $lockedUser->username,
                null,
                ['method' => 'captain_authorized_in_person_password_verified'],
                'Secretary completed Captain-authorized authenticator recovery after in-person identity verification.',
                $request
            );

            return true;
        });

        if (! $committed) {
            $this->clearSecretaryAuthenticatorRecovery($request);

            return redirect()->route('login')->withErrors([
                'username' => 'The account changed during recovery. No authenticator changes were made. Ask the Captain to authorize recovery again.',
            ]);
        }

        $this->clearSecretaryAuthenticatorRecovery($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with(
            'success',
            'Authenticator recovery completed. Sign in with your account password and the new authenticator.'
        );
    }

    public function showSetup(Request $request): Response|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return $this->restartLogin($request);
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->route('dashboard');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        if (
            $this->recoveryCodesPending($request)
            && $this->sessionRecoveryCodes($request) !== null
        ) {
            return redirect()->route('two-factor.recovery-codes');
        }

        $google2fa = app(Google2FA::class);

        if (empty($user->two_factor_secret)) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'e-Poblacion'),
            $user->username ?: $user->email,
            $user->two_factor_secret
        );

        return $this->sensitiveView('auth.two-factor-setup', [
            'qrCodeSvg' => QrCode::size(220)
                ->margin(1)
                ->generate($qrCodeUrl)
                ->toHtml(),
            'secretKey' => $user->two_factor_secret,
        ]);
    }

    public function confirmSetup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            return $this->restartLogin($request);
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->route('dashboard');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        if (empty($user->two_factor_secret)) {
            return redirect()->route('two-factor.setup')->withErrors([
                'code' => 'Your setup session expired. Please try again.',
            ]);
        }

        $key = $this->setupRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        if (! app(Google2FA::class)->verifyKey(
            $user->two_factor_secret,
            $validated['code'],
            self::TOTP_WINDOW
        )) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'code' => 'The authenticator code is invalid. Please try again.',
            ]);
        }

        if (! $this->claimTotpCode($user, $validated['code'])) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return back()->withErrors([
                'code' => 'That code was already used. Wait for the next code in your authenticator app and try again.',
            ]);
        }

        RateLimiter::clear($key);

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->two_factor_recovery_codes = json_encode(
            $recoveryCodes,
            JSON_THROW_ON_ERROR
        );
        $user->save();

        $request->session()->put([
            'two_factor_recovery_codes_pending' => true,
            'recovery_codes' => $recoveryCodes,
        ]);

        return redirect()->route('two-factor.recovery-codes');
    }

    public function showRecoveryCodes(
        Request $request
    ): Response|RedirectResponse {
        $user = $this->pendingUser($request);

        if (! $user) {
            return $this->restartLogin($request);
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->route('dashboard');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $codes = $this->sessionRecoveryCodes($request);

        if (! $this->recoveryCodesPending($request) || $codes === null) {
            $request->session()->forget([
                'two_factor_recovery_codes_pending',
                'recovery_codes',
            ]);

            return redirect()->route('two-factor.setup')->withErrors([
                'code' => 'Your recovery codes are no longer available. Enter a new authenticator code to generate a fresh set.',
            ]);
        }

        return $this->sensitiveView('auth.two-factor-recovery-codes', [
            'recoveryCodes' => $codes,
        ]);
    }

    public function finishSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'saved_confirmation' => ['required', 'accepted'],
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            return $this->restartLogin($request);
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->route('dashboard');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $codes = $this->sessionRecoveryCodes($request);

        if (! $this->recoveryCodesPending($request) || $codes === null) {
            $request->session()->forget([
                'two_factor_recovery_codes_pending',
                'recovery_codes',
            ]);

            return redirect()->route('two-factor.setup')->withErrors([
                'code' => 'Your recovery codes are no longer available. Enter a new authenticator code to generate a fresh set.',
            ]);
        }

        if (
            empty($user->two_factor_secret)
            || $this->getRecoveryCodes($user) !== $codes
        ) {
            $request->session()->forget([
                'two_factor_recovery_codes_pending',
                'recovery_codes',
            ]);

            return redirect()->route('two-factor.setup')->withErrors([
                'code' => 'Your setup could not be completed. Enter a new authenticator code to try again.',
            ]);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget([
            'two_factor_recovery_codes_pending',
            'recovery_codes',
        ]);

        $request->session()->regenerate();

        $request->session()->put([
            'two_factor_pending_user_id' => $user->getAuthIdentifier(),
            'two_factor_passed' => true,
        ]);

        return redirect()->route('dashboard')->with(
            'success',
            'Two-factor authentication has been set up successfully.'
        );
    }

    public function showChallenge(
        Request $request
    ): Response|RedirectResponse {
        $user = $this->pendingUser($request);

        if (! $user) {
            return $this->restartLogin($request);
        }

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->route('dashboard');
        }

        return $this->sensitiveView('auth.two-factor-challenge', []);
    }

    public function sendEmailRecoveryCode(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (
            ! $user
            || ! $user->hasTwoFactorEnabled()
            || $request->session()->get('two_factor_passed') === true
        ) {
            $this->clearEmailRecovery($request);

            return $this->restartLogin($request);
        }

        if (! is_string($user->email) || trim($user->email) === '') {
            $this->clearEmailRecovery($request);

            return back()->withErrors([
                'code' => 'There is no registered email address for this account. Contact the system administrator for account recovery.',
            ]);
        }

        /*
         * Email recovery is permitted only for an address that has already
         * been verified. Do not send codes to unverified account addresses.
         */
        if (! $user->email_verified_at) {
            $this->clearEmailRecovery($request);

            return back()->withErrors([
                'code' => 'The registered email address has not been verified. Contact the system administrator to verify it before using email recovery.',
            ]);
        }

        $sendKey = $this->emailRecoverySendRateLimitKey($user);

        if (RateLimiter::tooManyAttempts(
            $sendKey,
            self::EMAIL_RECOVERY_SEND_LIMIT
        )) {
            $seconds = RateLimiter::availableIn($sendKey);

            return back()->withErrors([
                'code' => "Too many email-code requests. Please wait {$seconds} seconds before requesting another code.",
            ]);
        }

        $this->clearEmailRecovery($request);
        RateLimiter::hit($sendKey, self::EMAIL_RECOVERY_SEND_DECAY_SECONDS);

        $code = (string) random_int(100000, 999999);

        try {
            Mail::raw(
                "Your e-Poblacion two-factor recovery code is: {$code}\n\n" .
                "This code expires in 10 minutes and can only be used once.\n" .
                "If you did not request this code, do not share it with anyone and secure your account.\n\n" .
                "For your security, barangay staff will never ask you to tell them this code.",
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('e-Poblacion 2FA Recovery Code');
                }
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->clearEmailRecovery($request);

            return back()->withErrors([
                'code' => 'The recovery email could not be sent. Check the mail configuration or contact the system administrator. Your account has not been signed in.',
            ]);
        }

        $request->session()->put([
            'two_factor_email_recovery_user_id' =>
                $user->getAuthIdentifier(),
            'two_factor_email_recovery_code_hash' => Hash::make($code),
            'two_factor_email_recovery_expires_at' =>
                now()->timestamp + self::EMAIL_RECOVERY_CODE_TTL_SECONDS,
            'two_factor_email_recovery_password_hash' =>
                (string) $user->password,
            'two_factor_email_recovery_email' => $user->email,
        ]);

        return back()->with(
            'status',
            'A verification code has been sent to the registered email address for this account. The code expires in 10 minutes.'
        );
    }

    public function verifyEmailRecoveryCode(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email_code' => ['required', 'digits:6'],
        ]);

        $user = $this->pendingUser($request);

        if (
            ! $user
            || ! $user->hasTwoFactorEnabled()
            || $request->session()->get('two_factor_passed') === true
        ) {
            $this->clearEmailRecovery($request);
            $this->clearEmailReplacementAuthorization($request);

            return $this->restartLogin($request);
        }

        /*
         * Re-check verification at consumption time as well. Also bind the
         * code to the exact address it was sent to, so changing the account's
         * email during the code lifetime invalidates the pending recovery.
         */
        if (! $user->email_verified_at) {
            $this->clearEmailRecovery($request);
            $this->clearEmailReplacementAuthorization($request);

            return back()->withErrors([
                'email_code' => 'The registered email address is not verified. Contact the system administrator for account recovery.',
            ]);
        }

        $verifyKey = $this->emailRecoveryVerifyRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($verifyKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($verifyKey);

            return back()->withErrors([
                'email_code' => "Too many incorrect attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        $recoveryUserId = $request->session()->get(
            'two_factor_email_recovery_user_id'
        );
        $codeHash = $request->session()->get(
            'two_factor_email_recovery_code_hash'
        );
        $expiresAt = $request->session()->get(
            'two_factor_email_recovery_expires_at'
        );
        $passwordHash = $request->session()->get(
            'two_factor_email_recovery_password_hash'
        );
        $recoveryEmail = $request->session()->get(
            'two_factor_email_recovery_email'
        );

        $stateIsValid =
            (string) $recoveryUserId ===
                (string) $user->getAuthIdentifier()
            && is_string($recoveryEmail)
            && $recoveryEmail !== ''
            && is_string($user->email)
            && hash_equals($recoveryEmail, $user->email)
            && is_string($codeHash)
            && $codeHash !== ''
            && is_numeric($expiresAt)
            && (int) $expiresAt > now()->timestamp
            && is_string($passwordHash)
            && hash_equals($passwordHash, (string) $user->password);

        if (
            ! $stateIsValid
            || ! Hash::check($validated['email_code'], (string) $codeHash)
        ) {
            RateLimiter::hit($verifyKey, self::DECAY_SECONDS);

            if (! $stateIsValid) {
                $this->clearEmailRecovery($request);
            }

            return back()->withErrors([
                'email_code' => 'The email verification code is invalid, expired, or no longer available. Request a new code if necessary.',
            ]);
        }

        /*
         * Consume the email code and create a separate, short-lived
         * authorization for the lost-authenticator replacement flow.
         *
         * IMPORTANT: do not call completeChallenge() here. Email recovery
         * alone must not grant ordinary authenticated system access.
         */
        $this->clearEmailRecovery($request);
        RateLimiter::clear($verifyKey);

        $request->session()->put([
            'two_factor_email_replacement_user_id' =>
                $user->getAuthIdentifier(),
            'two_factor_email_replacement_password_hash' =>
                (string) $user->password,
            'two_factor_email_replacement_expires_at' =>
                now()->timestamp +
                self::EMAIL_RECOVERY_REPLACEMENT_TTL_SECONDS,
        ]);

        return redirect()
            ->route('settings.authenticator-replacement.page')
            ->with(
                'success',
                'Email recovery was verified. Confirm your current password to enroll a new authenticator. You will not receive normal system access until you sign in again with the new authenticator.'
            );
    }

    private function getRecoveryCodes(User $user): array
    {
        $stored = $user->two_factor_recovery_codes;

        if (is_array($stored)) {
            return array_values(array_filter(
                $stored,
                fn ($code) => is_string($code)
            ));
        }

        if (! is_string($stored) || $stored === '') {
            return [];
        }

        try {
            $codes = json_decode($stored, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (! is_array($codes)) {
            return [];
        }

        return array_values(array_filter(
            $codes,
            fn ($code) => is_string($code)
        ));
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $lockedUser
                || ! $lockedUser->isActive()
                || ! $lockedUser->hasValidSystemRole()
                || ! $lockedUser->canAccessSystem()
                || ! $lockedUser->hasTwoFactorEnabled()
            ) {
                return false;
            }

            $recoveryCodes = $this->getRecoveryCodes($lockedUser);

            foreach ($recoveryCodes as $index => $recoveryCode) {
                if (! hash_equals($recoveryCode, $code)) {
                    continue;
                }

                unset($recoveryCodes[$index]);

                $lockedUser->two_factor_recovery_codes = json_encode(
                    array_values($recoveryCodes),
                    JSON_THROW_ON_ERROR
                );
                $lockedUser->save();

                return true;
            }

            return false;
        });
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $user = $this->pendingUser($request);

        if (! $user) {
            return $this->restartLogin($request);
        }

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        if ($request->session()->get('two_factor_passed') === true) {
            return redirect()->route('dashboard');
        }

        $key = $this->challengeRateLimitKey($user);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'code' => "Too many attempts. Please wait {$seconds} seconds before trying again.",
            ]);
        }

        $code = trim($validated['code']);

        if (
            preg_match('/^\d{6}$/', $code) === 1
            && ! empty($user->two_factor_secret)
        ) {
            if (app(Google2FA::class)->verifyKey(
                $user->two_factor_secret,
                $code,
                self::TOTP_WINDOW
            )) {
                if (! $this->claimTotpCode($user, $code)) {
                    RateLimiter::hit($key, self::DECAY_SECONDS);

                    return back()->withErrors([
                        'code' => 'That code was already used. Wait for the next code in your authenticator app and try again.',
                    ]);
                }

                RateLimiter::clear($key);

                return $this->completeChallenge($request, $user);
            }
        }

        if ($this->consumeRecoveryCode($user, $code)) {
            RateLimiter::clear($key);

            return $this->completeChallenge($request, $user);
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return back()->withErrors([
            'code' => 'The authenticator or recovery code is invalid, or the recovery code has already been used.',
        ]);
    }

    private function completeChallenge(
        Request $request,
        User $user
    ): RedirectResponse {
        $currentUser = User::query()->find($user->getKey());

        if (
            ! $currentUser
            || ! $currentUser->isActive()
            || ! $currentUser->hasValidSystemRole()
            || ! $currentUser->canAccessSystem()
            || ! $currentUser->hasTwoFactorEnabled()
            || (string) $currentUser->getAuthIdentifier()
                !== (string) $request->session()->get(
                    'two_factor_pending_user_id'
                )
        ) {
            return $this->restartLogin($request);
        }

        $request->session()->regenerate();

        $request->session()->forget([
            'two_factor_recovery_codes_pending',
            'recovery_codes',
            ...self::EMAIL_RECOVERY_SESSION_KEYS,
            ...self::EMAIL_REPLACEMENT_AUTH_SESSION_KEYS,
        ]);

        $request->session()->put([
            'two_factor_pending_user_id' =>
                $currentUser->getAuthIdentifier(),
            'two_factor_passed' => true,
        ]);

        return redirect()->intended(route('dashboard'));
    }
}
