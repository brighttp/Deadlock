<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;

class MemoryProbe extends AbstractProbe
{
    public function kunci(): string
    {
        return 'memori_mb';
    }

    protected function label(): string
    {
        return 'Memori PHP (puncak)';
    }

    protected function satuan(): string
    {
        return 'MB';
    }

    protected function baca(): Metric
    {
        $limit = $this->keByte((string) ini_get('memory_limit'));

        return $this->metric(
            round(memory_get_peak_usage(true) / 1048576, 2),
            $limit > 0 ? round($limit / 1048576, 2) : null, // memory_limit -1 berarti tanpa batas
        );
    }

    private function keByte(string $nilai): int
    {
        $angka = (int) $nilai;

        return match (strtolower(substr(trim($nilai), -1))) {
            'g' => $angka * 1073741824,
            'm' => $angka * 1048576,
            'k' => $angka * 1024,
            default => $angka,
        };
    }
}
