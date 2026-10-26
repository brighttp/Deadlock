<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'unit_id', 'loan_date', 'due_date', 'return_date', 'fine_amount', 'status', 'return_status', 'return_requested_at', 'return_rejection_note'])]
class Loan extends Model
{
    public const STATUS_AKTIF = 'aktif';

    public const STATUS_SELESAI = 'selesai';

    public const RETURN_DIAJUKAN = 'diajukan';

    public const RETURN_DITOLAK = 'ditolak';

    protected $attributes = [
        'status' => self::STATUS_AKTIF,
        'fine_amount' => 0,
        'return_status' => null,
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'return_requested_at' => 'datetime',
            'fine_amount' => 'integer',
        ];
    }

    // Riwayat tetap tampil walau anggota/unit sudah di-soft delete.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    // Menunggu persetujuan pengembalian oleh Admin
    public function scopeMenungguPersetujuan($query)
    {
        return $query->aktif()->where('return_status', self::RETURN_DIAJUKAN);
    }

    // Terlambat tidak disimpan di DB: aktif dan due_date sudah lewat (tanggal dari Laravel, bukan CURRENT_DATE).
    public function scopeTerlambat($query)
    {
        return $query->aktif()->whereDate('due_date', '<', today()->toDateString());
    }

    public function isTerlambat(): bool
    {
        return $this->status === self::STATUS_AKTIF && $this->due_date->lt(today());
    }

    public function isMenungguPersetujuan(): bool
    {
        return $this->status === self::STATUS_AKTIF && $this->return_status === self::RETURN_DIAJUKAN;
    }

    public function isPengembalianDitolak(): bool
    {
        return $this->status === self::STATUS_AKTIF && $this->return_status === self::RETURN_DITOLAK;
    }

    // Hari terlambat per tanggal acuan (default hari ini); 0 jika dikembalikan tepat/sebelum jatuh tempo.
    public function hariTerlambat(?CarbonInterface $per = null): int
    {
        $per ??= $this->return_date ?? today();

        return max(0, (int) $this->due_date->diffInDays($per, false));
    }

    public function hitungDenda(?CarbonInterface $per = null): int
    {
        return $this->hariTerlambat($per) * config('gudang.denda_per_hari');
    }

    // Label kapital hanya untuk tampilan.
    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_SELESAI) {
            return 'Selesai';
        }

        if ($this->isMenungguPersetujuan()) {
            return 'Menunggu Persetujuan';
        }

        if ($this->isPengembalianDitolak()) {
            return 'Pengembalian Ditolak';
        }

        if ($this->isTerlambat()) {
            return 'Terlambat';
        }

        return 'Aktif';
    }
}
