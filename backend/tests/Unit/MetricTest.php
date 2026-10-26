<?php

namespace Tests\Unit;

use App\Monitoring\Metric;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MetricTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['gudang.monitoring.peringatan_persen' => 70, 'gudang.monitoring.kritis_persen' => 90]);
    }

    public static function kasusStatus(): array
    {
        // [nilai, batas, persen, status]
        return [
            'jauh di bawah batas' => [100, 1000, 10.0, Metric::NORMAL],
            'tepat 70% = peringatan' => [700, 1000, 70.0, Metric::PERINGATAN],
            'tepat 90% = kritis' => [450, 500, 90.0, Metric::KRITIS],
            'melewati batas' => [1500, 1000, 150.0, Metric::KRITIS],
            'tanpa batas atas' => [26, null, null, Metric::NORMAL],
        ];
    }

    #[DataProvider('kasusStatus')]
    public function test_status_dihitung_dari_persen_terhadap_batas(float $nilai, ?float $batas, ?float $persen, string $status): void
    {
        $metric = new Metric('uji', 'Uji', 'MB', $nilai, $batas);

        $this->assertSame($persen, $metric->persen());
        $this->assertSame($status, $metric->status());
    }

    public function test_metric_gagal_butuh_tindakan(): void
    {
        $metric = Metric::gagal('db', 'Database', 'ms', 'Connection refused', 1000);

        $this->assertSame(Metric::GAGAL, $metric->status());
        $this->assertTrue($metric->butuhTindakan());
        $this->assertSame('Gagal diukur', $metric->teks());
    }

    public function test_teks_menampilkan_nilai_dan_batas(): void
    {
        $this->assertSame('10,8 / 500 MB', (new Metric('db', 'DB', 'MB', 10.8, 500))->teks());
        $this->assertSame('26 MB', (new Metric('mem', 'Memori', 'MB', 26, null))->teks());
    }
}
