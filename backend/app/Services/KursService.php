<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Kurs Rupiah ke mata uang lain dari API Frankfurter (kurs referensi ECB, diperbarui tiap hari kerja).
 * Graceful failure: API gagal => kurs terakhir yang berhasil; belum pernah berhasil => tampil Rupiah.
 */
class KursService
{
    private const KUNCI_KURS = 'kurs:idr';

    private const KUNCI_CADANGAN = 'kurs:idr:terakhir';

    private const COBA_ULANG_MENIT = 5;

    /** Kurs yang sudah dimuat di request ini, supaya cache tidak dibaca berulang. */
    private ?array $kurs = null;

    /**
     * Kurs 1 Rupiah ke tiap mata uang, misalnya ['IDR' => 1.0, 'USD' => 0.000055].
     * Urutan sumber: cache -> API -> kurs terakhir yang berhasil diambil.
     */
    public function kurs(): array
    {
        if ($this->kurs !== null) {
            return $this->kurs;
        }

        $kurs = Cache::get(self::KUNCI_KURS);

        if (! is_array($kurs)) {
            $kurs = $this->ambilDariApi();

            if ($kurs !== null) {
                Cache::put(self::KUNCI_KURS, $kurs, now()->addMinutes(config('gudang.mata_uang.cache_menit')));
                Cache::forever(self::KUNCI_CADANGAN, $kurs);
            } else {
                // API gagal: pakai kurs terakhir, coba API lagi beberapa menit kemudian.
                $kurs = Cache::get(self::KUNCI_CADANGAN, ['IDR' => 1.0]);
                Cache::put(self::KUNCI_KURS, $kurs, now()->addMinutes(self::COBA_ULANG_MENIT));
            }
        }

        return $this->kurs = $kurs;
    }

    /** Mengubah nominal Rupiah ke mata uang tujuan; null bila kursnya tidak tersedia. */
    public function konversi(int $rupiah, string $tujuan): ?float
    {
        $kurs = $this->kurs()[$tujuan] ?? null;

        return $kurs === null ? null : $rupiah * $kurs;
    }

    /** Format nominal dalam mata uang tujuan; kembali ke Rupiah bila kurs tidak tersedia. */
    public function format(int $rupiah, string $tujuan): string
    {
        $info = config("gudang.mata_uang.daftar.{$tujuan}");
        $nilai = $tujuan === 'IDR' ? null : $this->konversi($rupiah, $tujuan);

        if ($info === null || $nilai === null) {
            return rupiah($rupiah);
        }

        return $info['simbol'].number_format($nilai, $info['desimal'], ',', '.');
    }

    private function ambilDariApi(): ?array
    {
        $tujuan = array_values(array_diff(array_keys(config('gudang.mata_uang.daftar')), ['IDR']));
        $timeout = config('gudang.mata_uang.timeout_detik');

        try {
            $respons = Http::acceptJson()
                ->connectTimeout($timeout)
                ->timeout($timeout)
                ->get(config('gudang.mata_uang.api_url'), [
                    'base' => 'IDR',
                    'symbols' => implode(',', $tujuan),
                ]);
        } catch (ConnectionException $e) {
            Log::warning('API kurs tidak dapat dihubungi.', ['pesan' => $e->getMessage()]);

            return null;
        }

        $rates = $respons->json('rates');

        if ($respons->failed() || ! is_array($rates)) {
            Log::warning('API kurs mengembalikan respons tidak valid.', ['status' => $respons->status()]);

            return null;
        }

        $rates = array_intersect_key($rates, array_flip($tujuan));

        return ['IDR' => 1.0] + array_map('floatval', $rates);
    }
}
