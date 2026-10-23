<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

// status sengaja tidak fillable: hanya diubah sistem saat pinjam/kembali.
#[Fillable(['unit_code', 'name', 'description'])]
class Unit extends Model
{
    use SoftDeletes;

    public const STATUS_TERSEDIA = 'tersedia';

    public const STATUS_DIPINJAM = 'dipinjam';

    protected $attributes = [
        'status' => self::STATUS_TERSEDIA,
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoan(): HasOne
    {
        return $this->hasOne(Loan::class)->where('status', Loan::STATUS_AKTIF);
    }

    public function isTersedia(): bool
    {
        return $this->status === self::STATUS_TERSEDIA;
    }

    public function scopeTersedia($query)
    {
        return $query->where('status', self::STATUS_TERSEDIA);
    }
}
