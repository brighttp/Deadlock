<?php

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Rumus denda & status di model Loan. Tidak menyentuh database.
 */
class LoanDendaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['gudang.denda_per_hari' => 15000]); // OQ-01
    }

    public static function kasusDenda(): array
    {
        // [jatuh tempo, tanggal kembali, hari terlambat, denda]
        return [
            'kembali sebelum jatuh tempo' => ['2026-10-06', '2026-10-04', 0, 0],
            'kembali tepat jatuh tempo' => ['2026-10-06', '2026-10-06', 0, 0],
            'terlambat 1 hari' => ['2026-10-06', '2026-10-07', 1, 15000],
            'terlambat 3 hari' => ['2026-10-06', '2026-10-09', 3, 45000],
            'terlambat lintas bulan' => ['2026-10-30', '2026-11-02', 3, 45000],
        ];
    }

    #[DataProvider('kasusDenda')]
    public function test_hitung_denda_per_tanggal_kembali(string $due, string $kembali, int $hari, int $denda): void
    {
        $loan = $this->loanDummy('2026-10-01', $due);
        $per = Carbon::parse($kembali);

        $this->assertSame($hari, $loan->hariTerlambat($per));
        $this->assertSame($denda, $loan->hitungDenda($per));
    }

    public function test_tanpa_parameter_memakai_return_date(): void
    {
        $loan = $this->loanDummy('2026-10-01', '2026-10-06', '2026-10-08');

        $this->assertSame(30000, $loan->hitungDenda());
    }

    public function test_tarif_mengikuti_config(): void
    {
        config(['gudang.denda_per_hari' => 5000]);
        $loan = $this->loanDummy('2026-10-01', '2026-10-06', '2026-10-08');

        $this->assertSame(10000, $loan->hitungDenda());
    }

    public function test_status_terlambat_dan_label(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 09:00', 'Asia/Jakarta'));

        $terlambat = $this->loanDummy('2026-10-01', '2026-10-06');
        $tepat = $this->loanDummy('2026-10-02', '2026-10-07');
        $selesai = $this->loanDummy('2026-10-01', '2026-10-06', '2026-10-06');

        $this->assertTrue($terlambat->isTerlambat());
        $this->assertSame('Terlambat', $terlambat->statusLabel());

        $this->assertFalse($tepat->isTerlambat()); // jatuh tempo hari ini belum terlambat
        $this->assertSame('Aktif', $tepat->statusLabel());

        $this->assertFalse($selesai->isTerlambat());
        $this->assertSame('Selesai', $selesai->statusLabel());
    }

    public function test_helper_format_rupiah(): void
    {
        $this->assertSame('Rp0', rupiah(0));
        $this->assertSame('Rp45.000', rupiah(45000));
        $this->assertSame('Rp1.500.000', rupiah(1500000));
    }
}
