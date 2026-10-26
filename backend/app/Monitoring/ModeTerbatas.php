<?php

namespace App\Monitoring;

use App\Models\Unit;
use Illuminate\Database\DetectsLostConnections;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDOException;
use Throwable;

/**
 * Graceful failure saat database tidak bisa dihubungi:
 * aplikasi tetap menampilkan katalog terakhir (read-only) dan peminjaman dinonaktifkan,
 * lalu kembali normal otomatis begitu database tersambung lagi.
 */
class ModeTerbatas
{
    use DetectsLostConnections;

    public const ALERT = 'database';

    private const FLAG = 'mode_terbatas';

    private const SNAPSHOT = 'monitoring/katalog-snapshot.json';

    public function __construct(private AlertService $alert) {}

    public function koneksiGagal(Throwable $e): bool
    {
        for ($x = $e; $x; $x = $x->getPrevious()) {
            // SQLSTATE kelas 08 = connection exception.
            if ($x instanceof PDOException && str_starts_with((string) ($x->errorInfo[0] ?? $x->getCode()), '08')) {
                return true;
            }
            if ($this->causedByLostConnection($x) || str_contains($x->getMessage(), 'password authentication failed')) {
                return true;
            }
        }

        return false;
    }

    public function masuk(Throwable $penyebab): void
    {
        Cache::add(self::FLAG, now()->timestamp);
        $this->alert->laporkan(self::ALERT, 'Database tidak dapat dihubungi',
            'Aplikasi berjalan dalam mode terbatas (katalog read-only, peminjaman dinonaktifkan). Penyebab: '
            .mb_strimwidth($penyebab->getMessage(), 0, 300, '…'));
    }

    public function aktif(): bool
    {
        return Cache::has(self::FLAG);
    }

    public function sejak(): ?int
    {
        return Cache::get(self::FLAG);
    }

    /** Cek koneksi; jika database sudah bisa dihubungi, keluar dari mode terbatas. */
    public function cobaPulihkan(): bool
    {
        if (! $this->aktif()) {
            return true;
        }

        try {
            DB::select('SELECT 1');
        } catch (Throwable) {
            return false;
        }

        Cache::forget(self::FLAG);
        $this->alert->pulihkan(self::ALERT);

        return true;
    }

    public function simpanSnapshotKatalog(): void
    {
        $units = Unit::with('categories:id,name')->orderBy('name')->orderBy('unit_code')->get()
            ->map(fn (Unit $u) => [
                'kode' => $u->unit_code,
                'nama' => $u->name,
                'kategori' => $u->categories->pluck('name')->implode(', '),
                'status' => $u->status,
            ]);

        Storage::disk('local')->put(self::SNAPSHOT, json_encode([
            'waktu' => now()->toIso8601String(),
            'units' => $units,
        ]));
    }

    public function snapshotKatalog(): ?array
    {
        $isi = Storage::disk('local')->get(self::SNAPSHOT);

        return $isi ? json_decode($isi, true) : null;
    }
}
