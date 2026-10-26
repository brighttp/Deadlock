<?php

namespace App\Monitoring;

/**
 * Satu hasil pengukuran resource beserta batas atasnya.
 */
final class Metric
{
    public const NORMAL = 'normal';

    public const PERINGATAN = 'peringatan';

    public const KRITIS = 'kritis';

    public const GAGAL = 'gagal';

    public function __construct(
        public readonly string $kunci,
        public readonly string $label,
        public readonly string $satuan,
        public readonly ?float $nilai,
        public readonly ?float $batas = null,     // null = tidak punya batas atas
        public readonly ?string $pesanGagal = null,
    ) {}

    public static function gagal(string $kunci, string $label, string $satuan, string $pesan, ?float $batas = null): self
    {
        return new self($kunci, $label, $satuan, null, $batas, $pesan);
    }

    public function persen(): ?float
    {
        if ($this->nilai === null || ! $this->batas) {
            return null;
        }

        return round($this->nilai / $this->batas * 100, 1);
    }

    public function status(): string
    {
        if ($this->nilai === null) {
            return self::GAGAL;
        }

        $persen = $this->persen();

        return match (true) {
            $persen === null => self::NORMAL,
            $persen >= config('gudang.monitoring.kritis_persen') => self::KRITIS,
            $persen >= config('gudang.monitoring.peringatan_persen') => self::PERINGATAN,
            default => self::NORMAL,
        };
    }

    public function butuhTindakan(): bool
    {
        return in_array($this->status(), [self::KRITIS, self::GAGAL], true);
    }

    public function teks(): string
    {
        if ($this->nilai === null) {
            return 'Gagal diukur';
        }

        $batas = $this->batas ? ' / '.$this->angka($this->batas) : '';

        return $this->angka($this->nilai).$batas.' '.$this->satuan;
    }

    private function angka(float $nilai): string
    {
        return number_format($nilai, floor($nilai) == $nilai ? 0 : 1, ',', '.');
    }
}
