<?php

namespace Tests\Feature;

use App\Monitoring\AlertService;
use App\Monitoring\Metric;
use App\Monitoring\ModeTerbatas;
use App\Monitoring\Probes\ResourceProbe;
use App\Monitoring\ResourceMonitor;
use App\Notifications\AlertAplikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Alert notification (unit 15) dan pemantauan resource (unit 16).
 */
class MonitoringAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Notification::fake();
        config(['gudang.alert.email' => 'komandan@haven.test', 'gudang.alert.ulang_menit' => 30]);
    }

    public function test_alert_dikirim_sekali_lalu_diulang_sesuai_frekuensi(): void
    {
        $alert = app(AlertService::class);

        $this->assertTrue($alert->laporkan('uji', 'Disk penuh', 'Sisa 1%'));
        $this->assertFalse($alert->laporkan('uji', 'Disk penuh', 'Sisa 1%'), 'Belum 30 menit: tidak dikirim ulang.');

        $this->travel(31)->minutes();
        $this->assertTrue($alert->laporkan('uji', 'Disk penuh', 'Sisa 1%'));

        Notification::assertSentOnDemandTimes(AlertAplikasi::class, 2);
        $this->assertSame(2, $alert->aktif()['uji']['jumlah_kirim']);
    }

    public function test_kabar_pulih_hanya_dikirim_jika_ada_alert_aktif(): void
    {
        $alert = app(AlertService::class);

        $this->assertFalse($alert->pulihkan('uji'));

        $alert->laporkan('uji', 'Disk penuh', 'Sisa 1%');
        $this->assertTrue($alert->pulihkan('uji'));
        $this->assertSame([], $alert->aktif());

        Notification::assertSentOnDemand(AlertAplikasi::class, fn (AlertAplikasi $n, $channels, $notifiable) => $n->pulih
            && $notifiable->routes['mail'] === 'komandan@haven.test');
    }

    public function test_perintah_pantau_mengirim_alert_saat_kritis_dan_pulih_saat_normal(): void
    {
        $this->pakaiProbe(new Metric('db_latensi_ms', 'Latensi database', 'ms', 1500, 1000));

        $this->artisan('gudang:pantau')->assertFailed();
        $this->assertArrayHasKey('resource:db_latensi_ms', app(AlertService::class)->aktif());

        $this->pakaiProbe(new Metric('db_latensi_ms', 'Latensi database', 'ms', 120, 1000));

        $this->artisan('gudang:pantau')->assertSuccessful();
        $this->assertSame([], app(AlertService::class)->aktif());
        $this->assertNotNull(app(ModeTerbatas::class)->snapshotKatalog(), 'Salinan katalog disimpan saat database sehat.');
        $this->assertCount(2, Storage::disk('local')->json('monitoring/riwayat.json'));
    }

    public function test_database_mati_menampilkan_mode_terbatas_lalu_pulih_otomatis(): void
    {
        $anggota = $this->buatAnggota();
        $this->buatUnit(['unit_code' => 'SNAP-01', 'name' => 'Busur Silang']);
        app(ModeTerbatas::class)->simpanSnapshotKatalog();
        $koneksiAsli = config('database.default');

        // Simulasi database mati: arahkan ke port yang tidak melayani apa pun.
        config(['database.connections.mati' => [
            'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => 1, 'database' => 'x',
            'username' => 'x', 'password' => 'x', 'options' => [\PDO::ATTR_TIMEOUT => 2],
        ]]);
        config(['database.default' => 'mati']);

        $this->actingAs($anggota)->get('/katalog')
            ->assertStatus(503)
            ->assertSee('Mode Terbatas')
            ->assertSee('SNAP-01')
            ->assertSee('Busur Silang');

        $this->assertTrue(app(ModeTerbatas::class)->aktif());
        $this->assertArrayHasKey(ModeTerbatas::ALERT, app(AlertService::class)->aktif());

        // Database kembali: request berikutnya normal dan mode terbatas dimatikan otomatis.
        config(['database.default' => $koneksiAsli]);

        $this->actingAs($anggota)->get('/katalog')->assertOk();
        $this->assertFalse(app(ModeTerbatas::class)->aktif());
        $this->assertArrayNotHasKey(ModeTerbatas::ALERT, app(AlertService::class)->aktif());
    }

    public function test_error_biasa_tidak_dianggap_database_mati(): void
    {
        $this->assertFalse(app(ModeTerbatas::class)->koneksiGagal(new \RuntimeException('Pembagian dengan nol')));
    }

    public function test_halaman_monitoring_hanya_untuk_admin(): void
    {
        $this->actingAs($this->buatAnggota())->get('/monitoring')->assertForbidden();

        $this->actingAs($this->buatAdmin())->get('/monitoring')
            ->assertOk()
            ->assertSee('Latensi database')
            ->assertSee('Ukuran database')
            ->assertSee('Memori PHP');
    }

    public function test_tombol_jalankan_pemeriksaan_menyimpan_sampel(): void
    {
        $this->actingAs($this->buatAdmin())
            ->from('/monitoring')
            ->post('/monitoring/pantau')
            ->assertRedirect('/monitoring');

        $this->assertCount(1, Storage::disk('local')->json('monitoring/riwayat.json'));
    }

    private function pakaiProbe(Metric $metric): void
    {
        $probe = new class($metric) implements ResourceProbe
        {
            public function __construct(private Metric $metric) {}

            public function ukur(): Metric
            {
                return $this->metric;
            }
        };

        $this->app->instance(ResourceMonitor::class, new ResourceMonitor([$probe]));
    }
}
