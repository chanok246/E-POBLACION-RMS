<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class SettingsController extends Controller
{
    private const EMAIL_VERIFICATION_TTL_MINUTES = 10;
    private const EMAIL_VERIFICATION_MAX_SENDS = 3;
    private const EMAIL_VERIFICATION_MAX_ATTEMPTS = 5;

    /**
     * Display the authenticated user's personal account settings.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $session = $request->session();

        $replacementStage = null;
        $replacementQrCodeSvg = null;
        $replacementSecretKey = null;
        $replacementRecoveryCodes = null;

        $replacementUserId = $session->get(
            'authenticator_replacement_user_id'
        );

        $replacementSecret = $session->get(
            'authenticator_replacement_new_secret'
        );

        $stage = $session->get(
            'authenticator_replacement_stage'
        );

        $eligibleForReplacementDisplay =
            $user instanceof User
            && $user->isActive()
            && $user->hasValidSystemRole()
            && $user->hasTwoFactorEnabled()
            && $session->get('two_factor_passed') === true
            && (string) $session->get('two_factor_pending_user_id')
                === (string) $user->getAuthIdentifier()
            && $replacementUserId !== null
            && (string) $replacementUserId
                === (string) $user->getAuthIdentifier()
            && is_string($replacementSecret)
            && $replacementSecret !== '';

        if (
            $eligibleForReplacementDisplay
            && $stage === 'verify_new'
        ) {
            $google2fa = app(Google2FA::class);

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name', 'e-Poblacion'),
                $user->username ?: $user->email,
                $replacementSecret
            );

            $replacementQrCodeSvg = QrCode::size(220)
                ->margin(1)
                ->generate($qrCodeUrl)
                ->toHtml();

            $replacementSecretKey = $replacementSecret;
            $replacementStage = 'verify_new';
        }

        if (
            $eligibleForReplacementDisplay
            && $stage === 'confirm_recovery_codes'
        ) {
            $codes = $session->get(
                'authenticator_replacement_recovery_codes'
            );

            if (
                is_array($codes)
                && count($codes) === 8
                && collect($codes)->every(
                    fn ($code) => is_string($code) && $code !== ''
                )
            ) {
                $replacementRecoveryCodes = array_values($codes);
                $replacementStage = 'confirm_recovery_codes';
            }
        }

        return response()
            ->view('settings.index', [
                'user' => $user,
                'replacementStage' => $replacementStage,
                'replacementQrCodeSvg' => $replacementQrCodeSvg,
                'replacementSecretKey' => $replacementSecretKey,
                'replacementRecoveryCodes' => $replacementRecoveryCodes,
            ])
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, private'
            )
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Send an email verification code to the address the user wants
     * to register for account recovery.
     *
     * The address is not changed until the code is verified.
     * Requires the current password and an active authorized account.
     */
    public function sendEmailVerificationCode(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (
            ! $user instanceof User
            || ! $user->isActive()
            || ! $user->hasValidSystemRole()
            || ! $user->canAccessSystem()
        ) {
            abort(403, 'Your account is not authorized to verify an email address.');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'current_password' => [
                'required',
                'current_password',
            ],
        ]);

        $email = mb_strtolower(trim($validated['email']));

        if (
            ! empty($user->getAttribute('google_id'))
            && strcasecmp($email, (string) $user->email) !== 0
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Your account has a linked Google identity. Do not change this email until a safe Google account unlinking process is available.',
                ]);
        }

        if (
            strcasecmp($email, (string) $user->email) === 0
            && $user->email_verified_at !== null
        ) {
            return redirect()
                ->route('settings.index')
                ->with('success', 'Your current email address is already verified.');
        }

        $sendKey = 'email-verification-send:' . $user->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts(
            $sendKey,
            self::EMAIL_VERIFICATION_MAX_SENDS
        )) {
            $seconds = RateLimiter::availableIn($sendKey);

            return back()->withErrors([
                'email' => "Too many verification requests. Please try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($sendKey, 900);

        $code = (string) random_int(100000, 999999);

        $cacheKey = 'email-verification:' . $user->getAuthIdentifier();

        Cache::put(
            $cacheKey,
            [
                'email' => $email,
                'code_hash' => Hash::make($code),
            ],
            now()->addMinutes(self::EMAIL_VERIFICATION_TTL_MINUTES)
        );

        $message = implode("\n", [
            'Your e-Poblacion email verification code is:',
            '',
            $code,
            '',
            'This code expires in 10 minutes.',
            'If you did not request this code, you can ignore this email.',
            'Do not share this code with anyone.',
        ]);

        try {
            Mail::raw($message, function ($mail) use ($email): void {
                $mail
                    ->to($email)
                    ->subject('e-Poblacion Email Verification Code');
            });
        } catch (Throwable $exception) {
            report($exception);

            Cache::forget($cacheKey);

            return back()->withErrors([
                'email' => 'The verification email could not be sent. Check the mail configuration or try again later.',
            ]);
        }

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'A verification code was requested for the supplied email address. Enter the code before it expires.'
            );
    }

    /**
     * Verify the email code and register the verified email address.
     */
    public function verifyEmailAddress(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if (
            ! $user instanceof User
            || ! $user->isActive()
            || ! $user->hasValidSystemRole()
            || ! $user->canAccessSystem()
        ) {
            abort(403, 'Your account is not authorized to verify an email address.');
        }

        $validated = $request->validate([
            'verification_code' => [
                'required',
                'digits:6',
            ],
        ]);

        $userId = (string) $user->getAuthIdentifier();

        $verifyKey = 'email-verification-attempts:' . $userId;
        $cacheKey = 'email-verification:' . $userId;

        if (RateLimiter::tooManyAttempts(
            $verifyKey,
            self::EMAIL_VERIFICATION_MAX_ATTEMPTS
        )) {
            Cache::forget($cacheKey);

            $seconds = RateLimiter::availableIn($verifyKey);

            return back()->withErrors([
                'verification_code' => "Too many incorrect attempts. Request a new code after {$seconds} seconds.",
            ]);
        }

        $challenge = Cache::get($cacheKey);

        if (
            ! is_array($challenge)
            || ! isset($challenge['email'], $challenge['code_hash'])
            || ! is_string($challenge['email'])
            || ! is_string($challenge['code_hash'])
        ) {
            return back()->withErrors([
                'verification_code' => 'The verification code has expired or no code is pending. Request a new code.',
            ]);
        }

        if (! Hash::check(
            $validated['verification_code'],
            $challenge['code_hash']
        )) {
            RateLimiter::hit($verifyKey, 600);

            if (RateLimiter::tooManyAttempts(
                $verifyKey,
                self::EMAIL_VERIFICATION_MAX_ATTEMPTS
            )) {
                Cache::forget($cacheKey);
            }

            return back()->withErrors([
                'verification_code' => 'The verification code is incorrect or expired.',
            ]);
        }

        $pendingEmail = mb_strtolower(trim($challenge['email']));

        if (
            ! empty($user->getAttribute('google_id'))
            && strcasecmp($pendingEmail, (string) $user->email) !== 0
        ) {
            Cache::forget($cacheKey);

            return back()->withErrors([
                'email' => 'Your account has a linked Google identity. The email address cannot be changed through this verification process.',
            ]);
        }

        DB::transaction(function () use (
            $user,
            $pendingEmail
        ): void {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $lockedUser instanceof User
                || ! $lockedUser->isActive()
                || ! $lockedUser->hasValidSystemRole()
                || ! $lockedUser->canAccessSystem()
            ) {
                abort(403, 'The account is no longer authorized to verify an email address.');
            }

            $emailInUse = User::query()
                ->where('email', $pendingEmail)
                ->where('id', '!=', $lockedUser->getKey())
                ->exists();

            if ($emailInUse) {
                throw ValidationException::withMessages([
                    'email' => 'That email address is already registered to another account. Request a new code for a different address.',
                ]);
            }

            $lockedUser->email = $pendingEmail;
            $lockedUser->email_verified_at = now();
            $lockedUser->save();
        });

        Cache::forget($cacheKey);
        RateLimiter::clear($verifyKey);
        RateLimiter::clear(
            'email-verification-send:' . $user->getAuthIdentifier()
        );

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Your email address has been verified successfully. It can now be used for account recovery.'
            );
    }

    /**
     * Generate a one-time recovery credential for the Captain.
     */
    public function generateCaptainRecoveryCredential(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $user = $request->user();

        if (
            ! $user instanceof User ||
            ! $user->isCaptain() ||
            ! $user->isActive() ||
            ! $user->canAccessSystem()
        ) {
            abort(
                403,
                'Only the authorized Captain can generate this recovery credential.'
            );
        }

        if (
            session('two_factor_passed') !== true ||
            (string) session('two_factor_pending_user_id')
                !== (string) $user->getAuthIdentifier() ||
            ! $user->hasTwoFactorEnabled()
        ) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'captain_recovery' => 'Complete two-factor authentication before generating a recovery credential.',
                ]);
        }

        if (
            ! Schema::hasColumn(
                'users',
                'captain_recovery_credential_hash'
            ) ||
            ! Schema::hasColumn(
                'users',
                'captain_recovery_credential_created_at'
            )
        ) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'captain_recovery' => 'Captain recovery is not ready because its database migration has not been applied. No credential was generated.',
                ]);
        }

        $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
        ]);

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $credentialCharacters = '';

        for ($i = 0; $i < 8; $i++) {
            $index = random_int(0, strlen($alphabet) - 1);
            $credentialCharacters .= $alphabet[$index];
        }

        $credential = substr($credentialCharacters, 0, 4)
            . '-'
            . substr($credentialCharacters, 4, 4);

        $credentialHash = Hash::make($credential);

        DB::transaction(function () use (
            $user,
            $credentialHash,
            $auditLog
        ): void {
            $captain = User::query()
                ->whereKey($user->getKey())
                ->where('role', User::ROLE_CAPTAIN)
                ->where('status', User::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (
                ! $captain instanceof User ||
                ! $captain->isCaptain() ||
                ! $captain->isActive() ||
                ! $captain->canAccessSystem()
            ) {
                abort(
                    403,
                    'The Captain account is no longer authorized to generate a recovery credential.'
                );
            }

            $captain->setAttribute(
                'captain_recovery_credential_hash',
                $credentialHash
            );

            $captain->setAttribute(
                'captain_recovery_credential_created_at',
                now()
            );

            $captain->save();

            $auditLog->log(
                action: 'Captain Recovery Credential Generated',
                module: 'Authentication',
                record: $captain,
                oldValues: null,
                newValues: [
                    'recovery_credential_generated' => true,
                    'storage' => 'hashed',
                    'credential_length' => 8,
                    'one_time_use' => true,
                    'automatic_expiry' => false,
                    'previous_credential_replaced' => true,
                ],
                description: 'The Captain generated or replaced a one-time password recovery credential.'
            );
        });

        $request->session()->flash(
            'captain_recovery_credential',
            $credential
        );

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'A new recovery credential was generated. Save it securely; it will be shown only once.'
            );
    }

    /**
     * Start linking the authenticated account to Google.
     */
    public function redirectToGoogleLink(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
        ]);

        $user = $request->user();

        if (! $user instanceof User) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'Please sign in before linking a Google account.',
                ]);
        }

        if (
            ! $user->isActive() ||
            ! $user->hasValidSystemRole() ||
            ! in_array($user->role, ['captain', 'secretary'], true)
        ) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'google' => 'Your account is not authorized to link Google Sign-In.',
                ]);
        }

        if (
            $request->session()->get('two_factor_passed') !== true
            || (string) $request->session()->get('two_factor_pending_user_id')
                !== (string) $user->getAuthIdentifier()
            || ! $user->hasTwoFactorEnabled()
        ) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'google' => 'Complete two-factor authentication before linking a Google account.',
                ]);
        }

        if (
            ! config('services.google.client_id') ||
            ! config('services.google.client_secret') ||
            ! config('services.google.redirect')
        ) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'google' => 'Google Sign-In is not fully configured. Please contact the system administrator.',
                ]);
        }

        if (! Schema::hasColumn('users', 'google_id')) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'google' => 'Google account linking is not ready because the database migration has not been applied. Your account has not been changed.',
                ]);
        }

        if ($user->getAttribute('google_id')) {
            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'google' => 'A Google account is already linked to your account.',
                ]);
        }

        $request->session()->forget('google_link_user_id');
        $request->session()->put('google_link_user_id', $user->getKey());

        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->forget('google_link_user_id');

            return redirect()
                ->route('settings.index')
                ->withErrors([
                    'google' => 'Unable to start Google account linking. Please try again later.',
                ]);
        }
    }

    /**
     * Update only the profile fields actually supplied by the form.
     *
     * Email changes must go through the email verification process.
     * Theme changes can be submitted separately through updateTheme().
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403, 'You must be signed in to update your profile.');
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'nickname' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'theme' => [
                'sometimes',
                'required',
                Rule::in(['light', 'dark']),
            ],
            'avatar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        /*
         * Email is not editable through the ordinary profile form.
         * If the request includes an email, it must match the current
         * address. Changing an email still requires email verification.
         */
        if (
            array_key_exists('email', $validated)
            && strcasecmp(
                trim((string) $validated['email']),
                (string) $user->email
            ) !== 0
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'To change your email address, use the email verification process in Settings first. Your email has not been changed.',
                ]);
        }

        $oldAvatarPath = $user->avatar;
        $newAvatarPath = null;

        /*
         * Store a new avatar before changing the user record.
         * Do not delete the previous photo until the new path is saved.
         */
        if ($request->hasFile('avatar')) {
            $newAvatarPath = $request->file('avatar')->store(
                'avatars',
                'public'
            );

            if (! is_string($newAvatarPath) || $newAvatarPath === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'avatar' => 'The profile photo could not be saved. Please try again.',
                    ]);
            }

            $user->avatar = $newAvatarPath;
        }

        /*
         * Update only fields included in this request.
         * Missing email/theme values will not be required or overwritten.
         */
        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('nickname', $validated)) {
            $user->nickname = $validated['nickname'] ?? null;
        }

        if (array_key_exists('theme', $validated)) {
            $user->theme = $validated['theme'];
        }

        if (
            array_key_exists('name', $validated)
            || array_key_exists('nickname', $validated)
            || array_key_exists('theme', $validated)
            || $newAvatarPath !== null
        ) {
            try {
                $user->save();
            } catch (Throwable $exception) {
                /*
                 * If the database save fails, remove the newly uploaded
                 * file so an unsuccessful update does not leave an orphan.
                 */
                if ($newAvatarPath !== null) {
                    Storage::disk('public')->delete($newAvatarPath);
                }

                throw $exception;
            }
        }

        /*
         * Delete the old avatar only after the new avatar path has
         * successfully been saved.
         */
        if (
            $newAvatarPath !== null
            && ! empty($oldAvatarPath)
            && $oldAvatarPath !== $newAvatarPath
        ) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                $newAvatarPath !== null
                    ? 'Your profile information and photo have been updated successfully.'
                    : 'Your profile information has been updated successfully.'
            );
    }

    /**
     * Update only the authenticated user's theme.
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => [
                'required',
                Rule::in(['light', 'dark']),
            ],
        ]);

        $user = $request->user();
        $user->theme = $validated['theme'];
        $user->save();

        return response()->json([
            'success' => true,
            'theme' => $user->theme,
            'message' => 'Appearance preference updated successfully.',
        ]);
    }

    /**
     * Remove the authenticated user's profile photo.
     */
    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! empty($user->avatar)) {
            $oldAvatarPath = $user->avatar;

            $user->avatar = null;
            $user->save();

            Storage::disk('public')->delete($oldAvatarPath);
        }

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Your profile photo has been removed successfully.'
            );
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $user = $request->user();
        $user->password = $validated['password'];

        /*
         * Rotate the remember token so existing persistent remember-me
         * credentials cannot continue to authenticate with the old token.
         */
        $user->setRememberToken(Str::random(60));

        $user->save();

        return redirect()
            ->route('settings.index')
            ->with(
                'success',
                'Your password has been changed successfully.'
            );
    }
}
