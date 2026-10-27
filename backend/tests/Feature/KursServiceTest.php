<?php

namespace Tests\Feature;

use App\Services\KursService;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Konversi mata uang (revisi asesor). Http::fake berperan sebagai stub API Frankfurter,
 * jadi test tidak pernah memanggil internet sungguhan.
 */
class KursServiceTest extends TestCase
{
    use RefreshDatabase;

    // Bentuk respons Frankfurter v2 /v2/rates: satu baris per pasangan mata uang.
    private const KURS_PALSU = [
        ['date' => '2026-10-27', 'base' => 'IDR', 'quote' => 'EUR', 'rate' => 0.000055],
        ['date' => '2026-10-27', 'base' => 'IDR', 'quote' => 'JPY', 'rate' => 0.0092],
        ['date' => '2026-10-27', 'base' => 'IDR', 'quote' => 'SGD', 'rate' => 0.00008],
        ['date' => '2026-10-27', 'base' => 'IDR', 'quote' => 'USD', 'rate' => 0.00006],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config(['gudang.mata_uang.cache_menit' => 60, 'gudang.denda_per_hari' => 15000]);
    }

    public function test_mengonversi_rupiah_dengan_kurs_dari_api(): void
    {
        Http::fake(['api.frankfurter.dev/*' => Http::response(self::KURS_PALSU)]);

        $kurs = new KursService;

        $this->assertEqualsWithDelta(0.9, $kurs->konversi(15000, 'USD'), 0.000001);
        $this->assertSame('$0,90', $kurs->format(15000, 'USD'));
        $this->assertSame('¥138', $kurs->format(15000, 'JPY'));

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.frankfurter.dev/v2/rates')
            && $request['base'] === 'IDR' && $request['quotes'] === 'USD,EUR,SGD,JPY');
    }

    public function test_kurs_disimpan_di_cache_sehingga_api_dipanggil_sekali(): void
    {
        Http::fake(['api.frankfurter.dev/*' => Http::response(self::KURS_PALSU)]);

        (new KursService)->kurs();
        (new KursService)->kurs();

        Http::assertSentCount(1);
    }

    public function test_api_gagal_memakai_kurs_terakhir_yang_berhasil(): void
    {
        Http::fakeSequence('api.frankfurter.dev/*')
            ->push(self::KURS_PALSU)
            ->pushStatus(500);

        $this->assertEqualsWithDelta(0.9, (new KursService)->konversi(15000, 'USD'), 0.000001);

        $this->travel(61)->minutes(); // cache kurs kedaluwarsa, API dipanggil lagi dan gagal

        $this->assertEqualsWithDelta(0.9, (new KursService)->konversi(15000, 'USD'), 0.000001);
        Http::assertSentCount(2);
    }

    public function test_respons_api_tidak_valid_dianggap_gagal(): void
    {
        // Bentuk lama v1 (objek "rates") dan respons error v2 sama-sama tidak dipakai.
        Http::fakeSequence('api.frankfurter.dev/*')
            ->push(['base' => 'IDR', 'rates' => ['USD' => 0.00006]])
            ->push(['status' => 422, 'message' => 'invalid currency: ABC'], 422);

        $this->assertSame(rupiah(15000), (new KursService)->format(15000, 'USD'));

        $this->travel(6)->minutes(); // lewat jeda coba ulang 5 menit

        $this->assertSame(rupiah(15000), (new KursService)->format(15000, 'USD'));
        Http::assertSentCount(2);
    }

    public function test_tanpa_kurs_sama_sekali_tetap_menampilkan_rupiah(): void
    {
        Http::fake(fn () => throw new ConnectionException('Koneksi gagal'));

        $this->assertSame(rupiah(15000), (new KursService)->format(15000, 'USD'));
    }

    public function test_rupiah_tidak_memanggil_api(): void
    {
        Http::fake();

        $this->assertSame('Rp15.000', (new KursService)->format(15000, 'IDR'));
        Http::assertNothingSent();
    }

    public function test_helper_uang_mengikuti_mata_uang_pilihan_user(): void
    {
        Http::fake(['api.frankfurter.dev/*' => Http::response(self::KURS_PALSU)]);

        $user = $this->buatAnggota();
        $user->currency = 'USD';
        $user->save();

        $this->actingAs($user);
        $this->assertSame('$0,90', uang(15000));
    }

    public function test_user_memilih_mata_uang_di_profil_dan_denda_tampil_dalam_mata_uang_itu(): void
    {
        Http::fake(['api.frankfurter.dev/*' => Http::response(self::KURS_PALSU)]);
        $this->travelTo(Carbon::parse('2026-10-10 10:00', 'Asia/Jakarta'));

        $anggota = $this->buatAnggota(['name' => 'Sarah']);
        app(LoanService::class)->pinjam($anggota, $this->buatUnit());
        $this->travelTo(Carbon::parse('2026-10-16 10:00', 'Asia/Jakarta')); // terlambat 1 hari = Rp15.000

        $this->actingAs($anggota)
            ->put(route('profil.update'), ['name' => 'Sarah', 'currency' => 'USD'])
            ->assertSessionHasNoErrors();

        $this->assertSame('USD', $anggota->fresh()->currency);
        $this->actingAs($anggota->fresh())->get(route('pinjaman-saya'))->assertSee('$0,90');
    }

    public function test_mata_uang_tidak_dikenal_ditolak(): void
    {
        $this->actingAs($this->buatAnggota())
            ->put(route('profil.update'), ['name' => 'Sarah', 'currency' => 'XYZ'])
            ->assertSessionHasErrors(['currency' => 'Mata uang tidak dikenal.']);
    }

    public function test_cetak_riwayat_tetap_rupiah_walau_admin_memilih_usd(): void
    {
        Http::fake(['api.frankfurter.dev/*' => Http::response(self::KURS_PALSU)]);

        $loan = app(LoanService::class)->pinjam($this->buatAnggota(), $this->buatUnit());
        $loan->update(['status' => 'selesai', 'return_date' => $loan->due_date->copy()->addDay(), 'fine_amount' => 15000]);

        $admin = $this->buatAdmin();
        $admin->currency = 'USD';
        $admin->save();

        $this->actingAs($admin)->get(route('riwayat.index'))->assertSee('$0,90');
        $this->actingAs($admin)->get(route('riwayat.cetak'))->assertSee('Rp15.000')->assertDontSee('$0,90');
    }
}
