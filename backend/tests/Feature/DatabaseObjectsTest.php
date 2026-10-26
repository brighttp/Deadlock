<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Function, view, trigger, dan stored procedure PostgreSQL (migration 000008 dan 000009).
 * Hanya jalan dengan phpunit.pgsql.xml; di SQLite otomatis dilewati.
 */
#[Group('pgsql')]
class DatabaseObjectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Objek SQL hanya ada di PostgreSQL. Jalankan: php artisan test --configuration=phpunit.pgsql.xml');
        }
    }

    public function test_hitung_denda_sama_dengan_rumus_di_model(): void
    {
        config(['gudang.denda_per_hari' => 15000]);

        foreach ([['2026-10-06', '2026-10-04'], ['2026-10-06', '2026-10-06'], ['2026-10-06', '2026-10-09']] as [$due, $per]) {
            $dariDb = DB::scalar('SELECT hitung_denda(?::date, ?::date, ?)', [$due, $per, 15000]);
            $dariModel = $this->loanDummy('2026-10-01', $due)->hitungDenda(Carbon::parse($per));

            $this->assertSame($dariModel, (int) $dariDb, "due {$due}, kembali {$per}");
        }
    }

    public function test_hari_ini_wib_sama_dengan_tanggal_laravel(): void
    {
        $this->assertSame(now('Asia/Jakarta')->toDateString(), DB::scalar('SELECT hari_ini_wib()::text'));
    }

    public function test_trigger_mengubah_status_unit_saat_pinjaman_dibuat_dan_selesai(): void
    {
        $unit = $this->buatUnit();

        // Insert langsung ke tabel (tanpa LoanService) supaya yang bekerja murni trigger.
        $loanId = DB::table('loans')->insertGetId([
            'user_id' => $this->buatAnggota()->id,
            'unit_id' => $unit->id,
            'loan_date' => '2026-10-01',
            'due_date' => '2026-10-06',
            'status' => Loan::STATUS_AKTIF,
        ]);
        $this->assertSame(Unit::STATUS_DIPINJAM, $unit->fresh()->status);

        DB::table('loans')->where('id', $loanId)->update(['status' => Loan::STATUS_SELESAI, 'return_date' => '2026-10-05']);
        $this->assertSame(Unit::STATUS_TERSEDIA, $unit->fresh()->status);
    }

    public function test_view_pinjaman_aktif_menghitung_hari_terlambat(): void
    {
        $hariIni = now('Asia/Jakarta')->startOfDay();
        $anggota = $this->buatAnggota(['name' => 'Ellie']);

        $terlambat = Loan::create([
            'user_id' => $anggota->id, 'unit_id' => $this->buatUnit(['unit_code' => 'UNIT-T'])->id,
            'loan_date' => $hariIni->copy()->subDays(8), 'due_date' => $hariIni->copy()->subDays(3),
        ]);
        Loan::create([
            'user_id' => $anggota->id, 'unit_id' => $this->buatUnit(['unit_code' => 'UNIT-A'])->id,
            'loan_date' => $hariIni, 'due_date' => $hariIni->copy()->addDays(5),
        ]);
        Loan::create([
            'user_id' => $anggota->id, 'unit_id' => $this->buatUnit(['unit_code' => 'UNIT-S'])->id,
            'loan_date' => $hariIni->copy()->subDays(10), 'due_date' => $hariIni->copy()->subDays(5),
            'return_date' => $hariIni->copy()->subDays(5), 'status' => Loan::STATUS_SELESAI,
        ]);

        $rows = DB::table('v_pinjaman_aktif')->orderBy('unit_code')->get()->keyBy('unit_code');

        $this->assertSame(['UNIT-A', 'UNIT-T'], $rows->keys()->all()); // yang selesai tidak ikut
        $this->assertSame('Ellie', $rows['UNIT-T']->nama_anggota);
        $this->assertSame($terlambat->id, $rows['UNIT-T']->loan_id);
        $this->assertSame(3, (int) $rows['UNIT-T']->hari_terlambat);
        $this->assertTrue((bool) $rows['UNIT-T']->terlambat);
        $this->assertSame(0, (int) $rows['UNIT-A']->hari_terlambat);
        $this->assertFalse((bool) $rows['UNIT-A']->terlambat);
    }

    public function test_procedure_pengembalian_mengembalikan_denda_dan_menyelesaikan_pinjaman(): void
    {
        $unit = $this->buatUnit();
        $loan = $this->pinjamanAktif($unit);

        // Input: id, tanggal kembali (terlambat 2 hari), tarif. Output: p_denda.
        $hasil = DB::selectOne('CALL proses_pengembalian(?, ?::date, ?, NULL)', [$loan->id, '2026-10-08', 15000]);

        $this->assertSame(30000, (int) $hasil->p_denda);
        $loan->refresh();
        $this->assertSame(Loan::STATUS_SELESAI, $loan->status);
        $this->assertSame('2026-10-08', $loan->return_date->toDateString());
        $this->assertSame(30000, $loan->fine_amount);
        $this->assertSame(Unit::STATUS_TERSEDIA, $unit->fresh()->status); // lewat trigger
    }

    public function test_procedure_menolak_input_tidak_valid(): void
    {
        $loan = $this->pinjamanAktif($this->buatUnit());
        DB::statement('CALL proses_pengembalian(?, ?::date, ?, NULL)', [$loan->id, '2026-10-06', 15000]);

        foreach ([
            [$loan->id, 15000, 'Pinjaman ini sudah dikembalikan.'],
            [999999, 15000, 'Pinjaman tidak ditemukan.'],
            [$loan->id, -1, 'Tarif denda tidak valid.'],
        ] as [$id, $tarif, $pesan]) {
            try {
                DB::transaction(fn () => DB::statement('CALL proses_pengembalian(?, ?::date, ?, NULL)', [$id, '2026-10-06', $tarif]));
                $this->fail("Procedure seharusnya menolak: {$pesan}");
            } catch (QueryException $e) {
                $this->assertStringContainsString($pesan, $e->getMessage());
            }
        }
    }

    public function test_rollback_membatalkan_dan_commit_menyimpan_pengembalian(): void
    {
        $unit = $this->buatUnit();
        $loan = $this->pinjamanAktif($unit);

        DB::beginTransaction();
        DB::statement('CALL proses_pengembalian(?, ?::date, ?, NULL)', [$loan->id, '2026-10-06', 15000]);
        DB::rollBack();

        $this->assertSame(Loan::STATUS_AKTIF, $loan->fresh()->status);
        $this->assertSame(Unit::STATUS_DIPINJAM, $unit->fresh()->status); // efek trigger ikut batal

        DB::beginTransaction();
        DB::statement('CALL proses_pengembalian(?, ?::date, ?, NULL)', [$loan->id, '2026-10-06', 15000]);
        DB::commit();

        $this->assertSame(Loan::STATUS_SELESAI, $loan->fresh()->status);
        $this->assertSame(Unit::STATUS_TERSEDIA, $unit->fresh()->status);
    }

    private function pinjamanAktif(Unit $unit): Loan
    {
        return Loan::create([
            'user_id' => $this->buatAnggota()->id,
            'unit_id' => $unit->id,
            'loan_date' => '2026-10-01',
            'due_date' => '2026-10-06',
        ]);
    }
}
