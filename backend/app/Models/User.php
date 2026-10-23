<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ANGGOTA = 'anggota';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Email selalu disimpan huruf kecil (unique index memakai LOWER(email)).
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtolower(trim($value)),
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->loans()->where('status', Loan::STATUS_AKTIF);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAnggota(): bool
    {
        return $this->role === self::ROLE_ANGGOTA;
    }

    public function scopeAnggota($query)
    {
        return $query->where('role', self::ROLE_ANGGOTA);
    }
}
