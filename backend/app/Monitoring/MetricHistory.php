<?php

namespace App\Monitoring;

use Illuminate\Support\Facades\Storage;

/**
 * Riwayat sampel monitoring disimpan di file (bukan database),
 * supaya tetap tercatat ketika database sedang bermasalah.
 */
class MetricHistory
{
    private const FILE = 'monitoring/riwayat.json';

    /** @param array<string, Metric> $metrics */
    public function simpan(array $metrics): void
    {
        $sampel = $this->semua();
        $sampel[] = [
            'waktu' => now()->toIso8601String(),
            'nilai' => array_map(fn (Metric $m) => $m->nilai, $metrics),
        ];
        $sampel = array_slice($sampel, -config('gudang.monitoring.riwayat_maks_sampel'));

        Storage::disk('local')->makeDirectory('monitoring');
        file_put_contents(Storage::disk('local')->path(self::FILE), json_encode($sampel), LOCK_EX);
    }

    public function semua(): array
    {
        $isi = Storage::disk('local')->get(self::FILE);

        return $isi ? (json_decode($isi, true) ?: []) : [];
    }

    /** Nilai satu resource dari sampel lama ke baru (null = gagal diukur). */
    public function seri(string $kunci): array
    {
        return array_map(fn ($s) => $s['nilai'][$kunci] ?? null, $this->semua());
    }
}
