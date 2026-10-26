<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Unit;
use App\Services\LoanService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gudang.denda_per_hari' => 15000, 'gudang.lama_pinjam_hari' => 5, 'gudang.maks_pinjaman_aktif' => 2]);
        $this->travelTo(Carbon::parse('2026-10-01 10:00', 'Asia/Jakarta'));
        $this->service = app(LoanService::class);
    }

    public function test_pinjam_membuat_pinjaman_aktif_dan_mengubah_status_unit(): void
    {
        $anggota = $this->buatAnggota();
        $unit = $this->buatUnit();

        $loan = $this->service->pinjam($anggota, $unit);

        $this->assertSame(Loan::STATUS_AKTIF, $loan->status);
        $this->assertSame('2026-10-01', $loan->loan_date->toDateString());
        $this->assertSame('2026-10-06', $loan->due_date->toDateString()); // +5 hari
        $this->assertSame(Unit::STATUS_DIPINJAM, $unit->fresh()->status);
    }

    public function test_pinjam_ditolak_jika_unit_sedang_dipinjam(): void
    {
        $unit = $this->buatUnit();
        $this->service->pinjam($this->buatAnggota(), $unit);

        $this->assertPesanValidasi(
            'Unit sedang dipinjam dan tidak dapat dipinjam.', // MSG-09
            fn () => $this->service->pinjam($this->buatAnggota(), $unit),
        );
        $this->assertSame(1, Loan::count());
    }

    public function test_pinjam_ditolak_jika_sudah_mencapai_batas_dua_unit(): void
    {
        $anggota = $this->buatAnggota();
        $this->service->pinjam($anggota, $this->buatUnit());
        $this->service->pinjam($anggota, $this->buatUnit());
        $unitKetiga = $this->buatUnit();

        $this->assertPesanValidasi(
            'Batas peminjaman tercapai. Maksimal 2 unit aktif.', // MSG-08
            fn () => $this->service->pinjam($anggota, $unitKetiga),
        );
        $this->assertSame(Unit::STATUS_TERSEDIA, $unitKetiga->fresh()->status);
    }

    public function test_kembali_tepat_waktu_tanpa_denda(): void
    {
        $unit = $this->buatUnit();
        $loan = $this->service->pinjam($this->buatAnggota(), $unit);

        $this->travelTo(Carbon::parse('2026-10-06 15:00', 'Asia/Jakarta'));
        $loan = $this->service->kembali($loan);

        $this->assertSame(Loan::STATUS_SELESAI, $loan->status);
        $this->assertSame('2026-10-06', $loan->return_date->toDateString());
        $this->assertSame(0, $loan->fine_amount);
        $this->assertSame(Unit::STATUS_TERSEDIA, $unit->fresh()->status);
    }

    public function test_kembali_terlambat_dikenai_denda_per_hari(): void
    {
        $loan = $this->service->pinjam($this->buatAnggota(), $this->buatUnit());

        $this->travelTo(Carbon::parse('2026-10-08 08:00', 'Asia/Jakarta')); // terlambat 2 hari
        $loan = $this->service->kembali($loan);

        $this->assertSame(30000, $loan->fine_amount);
        $this->assertSame(30000, Loan::find($loan->id)->fine_amount); // tersimpan di database
    }

    public function test_kembali_dua_kali_ditolak(): void
    {
        $loan = $this->service->pinjam($this->buatAnggota(), $this->buatUnit());
        $this->service->kembali($loan);

        $this->assertPesanValidasi(
            'Pinjaman ini sudah dikembalikan.',
            fn () => $this->service->kembali($loan),
        );
    }

    public function test_unit_bisa_dipinjam_lagi_setelah_dikembalikan(): void
    {
        $unit = $this->buatUnit();
        $loan = $this->service->pinjam($this->buatAnggota(), $unit);
        $this->service->kembali($loan);

        $loanBaru = $this->service->pinjam($this->buatAnggota(), $unit->fresh());

        $this->assertSame(Loan::STATUS_AKTIF, $loanBaru->status);
        $this->assertSame(2, $unit->loans()->count());
    }

    public function test_database_menolak_dua_pinjaman_aktif_untuk_unit_yang_sama(): void
    {
        // Pengaman terakhir jika dua request lolos cek aplikasi bersamaan (partial unique index).
        $unit = $this->buatUnit();
        $data = ['unit_id' => $unit->id, 'loan_date' => '2026-10-01', 'due_date' => '2026-10-06'];
        Loan::create([...$data, 'user_id' => $this->buatAnggota()->id]);

        $this->expectException(QueryException::class);
        Loan::create([...$data, 'user_id' => $this->buatAnggota()->id]);
    }

    private function assertPesanValidasi(string $pesan, callable $aksi): void
    {
        try {
            $aksi();
            $this->fail('ValidationException tidak dilempar.');
        } catch (ValidationException $e) {
            $this->assertSame($pesan, collect($e->errors())->flatten()->first());
        }
    }
}
