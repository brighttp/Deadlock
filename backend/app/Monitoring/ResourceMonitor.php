<?php

namespace App\Monitoring;

use App\Monitoring\Probes\DatabaseConnectionProbe;
use App\Monitoring\Probes\DatabaseLatencyProbe;
use App\Monitoring\Probes\DatabaseSizeProbe;
use App\Monitoring\Probes\DiskProbe;
use App\Monitoring\Probes\MemoryProbe;
use App\Monitoring\Probes\PhotoStorageProbe;
use App\Monitoring\Probes\ResourceProbe;

/**
 * Resource kritikal aplikasi: database (latensi, ukuran, koneksi), disk, memori PHP, dan foto profil.
 */
class ResourceMonitor
{
    /** @var ResourceProbe[] */
    private array $probes;

    public function __construct(?array $probes = null)
    {
        $this->probes = $probes ?? [
            new DatabaseLatencyProbe,
            new DatabaseSizeProbe,
            new DatabaseConnectionProbe,
            new DiskProbe,
            new MemoryProbe,
            new PhotoStorageProbe,
        ];
    }

    /** @return array<string, Metric> dikunci dengan Metric::$kunci */
    public function ukurSemua(): array
    {
        $hasil = [];
        foreach ($this->probes as $probe) {
            $metric = $probe->ukur();
            $hasil[$metric->kunci] = $metric;
        }

        return $hasil;
    }
}
