<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;
use Throwable;

/**
 * Probe yang gagal (misalnya database mati) tidak boleh membuat halaman monitoring ikut error:
 * kegagalan diubah menjadi Metric berstatus "gagal".
 */
abstract class AbstractProbe implements ResourceProbe
{
    abstract public function kunci(): string;

    abstract protected function label(): string;

    abstract protected function satuan(): string;

    abstract protected function baca(): Metric;

    protected function batas(): ?float
    {
        $batas = config('gudang.monitoring.batas.'.$this->kunci());

        return $batas === null ? null : (float) $batas;
    }

    protected function metric(?float $nilai, ?float $batas = null): Metric
    {
        return new Metric($this->kunci(), $this->label(), $this->satuan(), $nilai, $batas ?? $this->batas());
    }

    final public function ukur(): Metric
    {
        try {
            return $this->baca();
        } catch (Throwable $e) {
            return Metric::gagal($this->kunci(), $this->label(), $this->satuan(), $e->getMessage(), $this->batas());
        }
    }
}
