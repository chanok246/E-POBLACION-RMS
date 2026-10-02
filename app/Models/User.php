<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    public const ROLE_CAPTAIN = 'captain';

    public const ROLE_SECRETARY = 'secretary';

    /*
    |--------------------------------------------------------------------------
    | Account Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'nickname',
        'avatar',
        'theme',
        'email',
        'password',
        'role',
        'status',
        'document_processor',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Fields
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'document_processor' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Account Status
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /*
    |--------------------------------------------------------------------------
    | Role Checking
    |--------------------------------------------------------------------------
    */

    public function hasRole(string|array $roles): bool
    {
        return in_array(
            strtolower(trim((string) $this->role)),
            (array) $roles,
            true
        );
    }

    public function isCaptain(): bool
    {
        return $this->role === self::ROLE_CAPTAIN;
    }

    public function isSecretary(): bool
    {
        return $this->role === self::ROLE_SECRETARY;
    }

    public function hasValidSystemRole(): bool
    {
        return in_array(
            strtolower(trim((string) $this->role)),
            [
                self::ROLE_CAPTAIN,
                self::ROLE_SECRETARY,
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Document Processing Permission
    |--------------------------------------------------------------------------
    */

    public function canProcessDocuments(): bool
{
    return $this->isActive()
        && $this->hasRole([
            self::ROLE_CAPTAIN,
            self::ROLE_SECRETARY,
        ]);
}
    /*
    |--------------------------------------------------------------------------
    | Display Name
    |--------------------------------------------------------------------------
    |
    | Uses the user's nickname when available.
    | Otherwise, falls back to the registered full name.
    |
    */

    public function getDisplayNameAttribute(): string
    {
        return trim((string) $this->nickname) !== ''
            ? $this->nickname
            : $this->name;
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar
    |--------------------------------------------------------------------------
    |
    | Returns the stored avatar path if one exists.
    |
    */

    public function hasAvatar(): bool
    {
        return ! empty($this->avatar);
    }

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */

    public function getThemeAttribute(?string $value): string
{
    return in_array($value, [
        'light',
        'dark',
        'system',
    ], true)
        ? $value
        : 'system';
}
}