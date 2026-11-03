<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_CAPTAIN = 'captain';

    public const ROLE_SECRETARY = 'secretary';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * Attributes that may be mass-assigned.
     *
     * Administrative password changes must still be protected
     * by controller authorization and validation rules.
     */
    protected $fillable = [
        'name',
        'username',
        'nickname',
        'avatar',
        'theme',
        'email',
        'password',
        'role',
        'status',
        'document_processor',
    ];

    /**
     * Attributes excluded from array and JSON serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'google_id',
        'captain_recovery_credential_hash',
    ];

    /**
     * Attribute casts.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'document_processor' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'captain_recovery_credential_created_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the account is active.
     */
    public function isActive(): bool
    {
        return strtolower(trim((string) $this->status))
            === self::STATUS_ACTIVE;
    }

    /**
     * Check whether the account has one of the specified roles.
     *
     * Role comparisons are case-insensitive and ignore surrounding spaces.
     */
    public function hasRole(string|array $roles): bool
    {
        $currentRole = strtolower(trim((string) $this->role));

        $allowedRoles = array_map(
            static fn ($role): string => strtolower(trim((string) $role)),
            (array) $roles
        );

        return in_array($currentRole, $allowedRoles, true);
    }

    /**
     * Determine whether this account belongs to the Captain.
     */
    public function isCaptain(): bool
    {
        return $this->hasRole(self::ROLE_CAPTAIN);
    }

    /**
     * Determine whether this account belongs to the Secretary.
     */
    public function isSecretary(): bool
    {
        return $this->hasRole(self::ROLE_SECRETARY);
    }

    /**
     * Determine whether this account has a recognized system role.
     *
     * Only Captain and Secretary are recognized system roles.
     */
    public function hasValidSystemRole(): bool
    {
        return $this->hasRole([
            self::ROLE_CAPTAIN,
            self::ROLE_SECRETARY,
        ]);
    }

    /**
     * Determine whether two-factor authentication is configured
     * and confirmed for this account.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return ! empty($this->two_factor_secret)
            && $this->two_factor_confirmed_at !== null;
    }

    /**
     * Determine whether this account may access the system.
     *
     * The account must be active and hold a recognized system role.
     */
    public function canAccessSystem(): bool
    {
        return $this->isActive()
            && $this->hasValidSystemRole();
    }

    /**
     * Determine whether this account may process documents.
     */
    public function canProcessDocuments(): bool
    {
        return $this->canAccessSystem();
    }

    /**
     * Get the display name, preferring the nickname when provided.
     */
    public function getDisplayNameAttribute(): string
    {
        $nickname = trim((string) $this->nickname);

        if ($nickname !== '') {
            return $nickname;
        }

        return trim((string) $this->name);
    }

    /**
     * Determine whether the account has a profile photo.
     */
    public function hasAvatar(): bool
    {
        return trim((string) $this->avatar) !== '';
    }

    /**
     * Return a supported theme, falling back to system.
     */
    public function getThemeAttribute(?string $value): string
    {
        $theme = strtolower(trim((string) $value));

        return in_array(
            $theme,
            ['light', 'dark', 'system'],
            true
        )
            ? $theme
            : 'system';
    }
}
