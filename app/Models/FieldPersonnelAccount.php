<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use LogicException;

class FieldPersonnelAccount extends Authenticatable
{
    protected $table = 'field_personnel_accounts';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'position',
        'username',
        'login_password_hash',
        'account_active',
        'created_by',
        'password_updated_at',
        'last_login_at',
    ];

    protected $hidden = [
        'login_password_hash',
    ];

    protected function casts(): array
    {
        return [
            'account_active' => 'boolean',
            'password_updated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Tell Laravel which database column contains the password hash.
     */
    public function getAuthPasswordName(): string
    {
        return 'login_password_hash';
    }

    /**
     * Return the stored password hash for authentication validation.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->login_password_hash;
    }

    protected static function booted(): void
    {
        static::deleting(function (FieldPersonnelAccount $account): void {
            throw new LogicException(
                'Field Personnel accounts cannot be deleted. Deactivate the account instead.'
            );
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setLoginPassword(string $password): void
    {
        if ($password === '') {
            throw new \InvalidArgumentException(
                'The generated password cannot be empty.'
            );
        }

        $this->login_password_hash = Hash::make($password);
        $this->password_updated_at = now();
    }

    public function verifyLoginPassword(string $password): bool
    {
        if ($password === '' || empty($this->login_password_hash)) {
            return false;
        }

        return Hash::check($password, $this->login_password_hash);
    }

    public function hasLoginPassword(): bool
    {
        return !empty($this->login_password_hash);
    }

    public function isActive(): bool
    {
        return $this->account_active === true;
    }

    public function canAccessFieldCollection(): bool
    {
        return $this->isActive() && $this->hasLoginPassword();
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }
}
