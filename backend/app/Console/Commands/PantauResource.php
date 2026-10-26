<?php

namespace App\Console\Commands;

use App\Monitoring\AlertService;
use App\Monitoring\Metric;
use App\Monitoring\MetricHistory;
use App\Monitoring\ModeTerbatas;
use App\Monitoring\ResourceMonitor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Dijadwalkan tiap 5 menit (routes/console.php). Mengukur resource, menyimpan riwayat,
 * mengirim alert jika ada resource kritis/gagal, dan kabar pulih jika sudah normal.
 */
#[Signature('gudang:pantau')]
#[Description('Pantau resource aplikasi dan kirim alert jika bermasalah')]
class PantauResource extends Command
{
    public function handle(ResourceMonitor $monitor, MetricHistory $riwayat, AlertService $alert, ModeTerbatas $modeTerbatas): int
    {
        $metrics = $monitor->ukurSemua();
        $riwayat->simpan($metrics);

        foreach ($metrics as $metric) {
            $kunci = "resource:{$metric->kunci}";

            if ($metric->butuhTindakan()) {
                $alert->laporkan($kunci, "{$metric->label} {$metric->status()}", $metric->status() === Metric::GAGAL
                    ? "Gagal diukur: {$metric->pesanGagal}"
                    : "Terukur {$metric->teks()} ({$metric->persen()}% dari batas atas).");
            } else {
                $alert->pulihkan($kunci);
            }
        }

        // Database bisa dihubungi => pastikan keluar dari mode terbatas dan perbarui salinan katalog.
        if ($metrics['db_latensi_ms']->nilai !== null) {
            $modeTerbatas->cobaPulihkan();
            $modeTerbatas->simpanSnapshotKatalog();
        }

        $this->table(['Resource', 'Nilai', '%', 'Status'], array_map(fn (Metric $m) => [
            $m->label, $m->teks(), $m->persen() ?? '-', strtoupper($m->status()),
        ], $metrics));

        return collect($metrics)->contains(fn (Metric $m) => $m->butuhTindakan()) ? self::FAILURE : self::SUCCESS;
    }
}
