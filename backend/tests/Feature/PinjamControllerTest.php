<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Controller pinjam/kembali. Test ini berperan sebagai driver (memanggil route),
 * dan sebagian memakai stub LoanService supaya controller diuji terpisah dari logika bisnis.
 */
class PinjamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gudang.denda_per_hari' => 15000]);
        $this->travelTo(Carbon::parse('2026-10-01 10:00', 'Asia/Jakarta'));
    }

    public function test_anggota_meminjam_dari_katalog(): void
    {
        $anggota = $this->buatAnggota();
        $unit = $this->buatUnit();

        $this->actingAs($anggota)
            ->from('/katalog')
            ->post(route('pinjam', $unit))
            ->assertRedirect('/katalog')
            ->assertSessionHas('success', fn ($pesan) => str_starts_with($pesan, 'Peminjaman berhasil.')); // MSG-15

        $this->assertDatabaseHas('loans', ['user_id' => $anggota->id, 'unit_id' => $unit->id, 'status' => 'aktif']);
    }

    public function test_pesan_dari_service_diteruskan_ke_flash_error(): void
    {
        // Stub: service selalu menolak, sehingga yang diuji hanya cara controller menangani penolakan.
        $this->mock(LoanService::class, function (MockInterface $mock) {
            $mock->shouldReceive('pinjam')->once()->andThrow(
                ValidationException::withMessages(['pinjam' => 'Pesan dari stub.'])
            );
        });

        $this->actingAs($this->buatAnggota())
            ->from('/katalog')
            ->post(route('pinjam', $this->buatUnit()))
            ->assertRedirect('/katalog')
            ->assertSessionHas('error', 'Pesan dari stub.');

        $this->assertSame(0, Loan::count());
    }

    public function test_admin_memproses_pengembalian_terlambat(): void
    {
        $loan = app(LoanService::class)->pinjam($this->buatAnggota(), $this->buatUnit());
        $this->travelTo(Carbon::parse('2026-10-09 10:00', 'Asia/Jakarta')); // terlambat 3 hari

        $this->actingAs($this->buatAdmin())
            ->from('/pinjaman')
            ->post(route('pinjaman.kembali', $loan))
            ->assertRedirect('/pinjaman')
            ->assertSessionHas('success', 'Pengembalian diproses. Denda: Rp45.000.'); // MSG-16
    }

    public function test_pinjaman_saya_hanya_menampilkan_milik_sendiri(): void
    {
        $service = app(LoanService::class);
        $saya = $this->buatAnggota();
        $service->pinjam($saya, $this->buatUnit(['name' => 'Busur Milik Saya']));
        $service->pinjam($this->buatAnggota(), $this->buatUnit(['name' => 'Kapak Orang Lain']));

        $this->actingAs($saya)
            ->get(route('pinjaman-saya'))
            ->assertOk()
            ->assertSee('Busur Milik Saya')
            ->assertDontSee('Kapak Orang Lain');
    }
}
