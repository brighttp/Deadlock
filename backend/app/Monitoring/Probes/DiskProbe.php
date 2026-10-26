<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;
use RuntimeException;

class DiskProbe extends AbstractProbe
{
    public function kunci(): string
    {
        return 'disk_gb';
    }

    protected function label(): string
    {
        return 'Disk server (storage)';
    }

    protected function satuan(): string
    {
        return 'GB';
    }

    protected function baca(): Metric
    {
        // Pengecualian guideline: operator @ dipakai karena fungsi disk_* memunculkan warning
        // (bukan exception) di sebagian sistem; hasil false sudah ditangani di bawah.
        $total = @disk_total_space(storage_path());
        $bebas = @disk_free_space(storage_path());

        if (! $total || $bebas === false) {
            throw new RuntimeException('Kapasitas disk tidak dapat dibaca.');
        }

        // Batas = kapasitas total disk.
        return $this->metric(round(($total - $bebas) / 1073741824, 2), round($total / 1073741824, 2));
    }
}
