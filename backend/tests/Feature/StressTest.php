<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Stress test: data jauh di atas perkiraan pemakaian, lalu setiap skenario diulang berkali-kali.
 * Yang diukur: waktu respons (rata-rata, p95, maksimum), jumlah query per request, dan memori puncak.
 * Tidak ikut jalan otomatis. Jalankan:
 *   php artisan test --configuration=phpunit.pgsql.xml --group=stress
 * Hasil ditulis ke storage/app/stress/laporan-*.md dan .json.
 */
#[Group('stress')]
class StressTest extends TestCase
{
    use RefreshDatabase;

    private const UNIT = 1000;

    private const ANGGOTA = 300;

    private const RIWAYAT = 10000;

    private const ULANG = 30;

    private const SIKLUS_PINJAM = 200;

    private const BATAS_P95_MS = 1000;

    private array $hasil = [];

    public function test_stress_aplikasi_dengan_data_besar(): void
    {
        $mulaiSeed = hrtime(true);
        [$admin, $anggota] = $this->isiDataBesar();
        $detikSeed = round((hrtime(true) - $mulaiSeed) / 1e9, 1);

        $this->ukurHalaman('Dashboard admin', $admin, '/dashboard');
        $this->ukurHalaman('Katalog (cari + filter)', $anggota, '/katalog?q=senapan&status=tersedia');
        $this->ukurHalaman('Daftar pinjaman aktif', $admin, '/pinjaman');
        $this->ukurHalaman('Riwayat (paginasi)', $admin, '/riwayat');
        $this->ukurHalaman('Daftar anggota (cari)', $admin, '/anggota?q=survivor');
        $this->ukurHalaman('Cetak riwayat 1 bulan', $admin, '/riwayat/cetak?dari='.now()->subMonth()->toDateString().'&sampai='.now()->toDateString(), 5);
        $this->ukurHalaman('Cetak riwayat semua', $admin, '/riwayat/cetak', 3);
        $this->ukurSiklusPinjamKembali();

        $this->tulisLaporan($detikSeed);

        foreach ($this->hasil as $h) {
            $this->assertLessThan(self::BATAS_P95_MS, $h['p95_ms'], "p95 {$h['skenario']} melewati batas.");
            $this->assertSame(0, $h['gagal'], "Ada respons gagal pada {$h['skenario']}.");
        }
    }

    private function isiDataBesar(): array
    {
        $password = Hash::make('password123');
        $now = now();

        $admin = $this->buatAdmin();
        $anggota = $this->buatAnggota();

        foreach (array_chunk(range(1, self::ANGGOTA), 500) as $chunk) {
            DB::table('users')->insert(array_map(fn ($i) => [
                'name' => "Survivor {$i}", 'email' => "survivor{$i}@haven.test", 'password' => $password,
                'role' => User::ROLE_ANGGOTA, 'created_at' => $now, 'updated_at' => $now,
            ], $chunk));
        }

        $jenis = ['Senapan', 'Busur', 'Kapak', 'Radio', 'Medkit', 'Senter', 'Pisau', 'Tenda'];
        foreach (array_chunk(range(1, self::UNIT), 500) as $chunk) {
            DB::table('units')->insert(array_map(fn ($i) => [
                'unit_code' => sprintf('STR-%05d', $i), 'name' => $jenis[$i % count($jenis)]." Tipe {$i}",
                'status' => 'tersedia', 'created_at' => $now, 'updated_at' => $now,
            ], $chunk));
        }

        $userIds = DB::table('users')->where('role', User::ROLE_ANGGOTA)->pluck('id')->all();
        $unitIds = DB::table('units')->pluck('id')->all();

        // Riwayat selesai tersebar 2 tahun ke belakang (tidak memicu status unit "dipinjam").
        foreach (array_chunk(range(1, self::RIWAYAT), 1000) as $chunk) {
            DB::table('loans')->insert(array_map(function ($i) use ($userIds, $unitIds, $now) {
                $pinjam = $now->copy()->subDays(($i % 730) + 6)->toDateString();
                $tempo = $now->copy()->subDays(($i % 730) + 1)->toDateString();
                $kembali = $now->copy()->subDays(($i % 730) + 1 - ($i % 4))->toDateString();

                return [
                    'user_id' => $userIds[$i % count($userIds)], 'unit_id' => $unitIds[$i % count($unitIds)],
                    'loan_date' => $pinjam, 'due_date' => $tempo, 'return_date' => $kembali,
                    'fine_amount' => ($i % 4) * 15000, 'status' => Loan::STATUS_SELESAI,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }, $chunk));
        }

        return [$admin, $anggota];
    }

    private function ukurHalaman(string $skenario, User $user, string $url, int $ulang = self::ULANG): void
    {
        $waktu = [];
        $query = [];
        $gagal = 0;

        for ($i = 0; $i < $ulang; $i++) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $mulai = hrtime(true);

            $status = $this->actingAs($user)->get($url)->getStatusCode();

            $waktu[] = (hrtime(true) - $mulai) / 1e6;
            $query[] = count(DB::getQueryLog());
            DB::disableQueryLog();
            $gagal += $status === 200 ? 0 : 1;
        }

        $this->catat($skenario, "GET {$url}", $waktu, $query, $gagal);
    }

    private function ukurSiklusPinjamKembali(): void
    {
        $service = app(LoanService::class);
        $peminjam = User::where('email', 'like', 'survivor%')->limit(self::SIKLUS_PINJAM)->get();
        $units = DB::table('units')->limit(self::SIKLUS_PINJAM)->pluck('id');
        $waktu = [];
        $query = [];

        foreach ($peminjam as $i => $user) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $mulai = hrtime(true);

            $loan = $service->pinjam($user, Unit::find($units[$i]));
            $service->kembali($loan);

            $waktu[] = (hrtime(true) - $mulai) / 1e6;
            $query[] = count(DB::getQueryLog());
            DB::disableQueryLog();
        }

        $this->catat('Siklus pinjam + kembali', 'LoanService::pinjam + kembali', $waktu, $query, 0);
    }

    private function catat(string $skenario, string $aksi, array $waktu, array $query, int $gagal): void
    {
        sort($waktu);
        $this->hasil[] = [
            'skenario' => $skenario,
            'aksi' => $aksi,
            'ulang' => count($waktu),
            'rata_ms' => round(array_sum($waktu) / count($waktu), 1),
            'p95_ms' => round($waktu[(int) ceil(0.95 * count($waktu)) - 1], 1),
            'maks_ms' => round(end($waktu), 1),
            'query_per_request' => max($query),
            'gagal' => $gagal,
        ];
    }

    private function tulisLaporan(float $detikSeed): void
    {
        $meta = [
            'waktu' => now()->toIso8601String(),
            'database' => DB::getDriverName().' '.DB::scalar(DB::getDriverName() === 'pgsql' ? 'SHOW server_version' : 'SELECT sqlite_version()'),
            'php' => PHP_VERSION,
            'os' => PHP_OS_FAMILY,
            'data' => ['unit' => self::UNIT, 'anggota' => self::ANGGOTA, 'riwayat' => self::RIWAYAT],
            'detik_isi_data' => $detikSeed,
            'memori_puncak_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
            'batas_p95_ms' => self::BATAS_P95_MS,
            // Ukuran tabel + index setelah diisi, dasar perkiraan pertumbuhan database.
            'ukuran_tabel_kb' => DB::getDriverName() === 'pgsql'
                ? collect(['users', 'units', 'loans'])->mapWithKeys(fn ($t) => [$t => (int) round(DB::scalar('SELECT pg_total_relation_size(?)', [$t]) / 1024)])->all()
                : [],
        ];

        $md = "# Laporan Stress Test\n\n"
            ."- Waktu: {$meta['waktu']}\n- Database: {$meta['database']} · PHP {$meta['php']} · {$meta['os']}\n"
            .'- Data: '.self::UNIT.' unit, '.self::ANGGOTA.' anggota, '.self::RIWAYAT." riwayat pinjaman (diisi dalam {$detikSeed} detik)\n"
            ."- Memori PHP puncak: {$meta['memori_puncak_mb']} MB\n"
            .($meta['ukuran_tabel_kb'] ? '- Ukuran tabel + index (KB): '.collect($meta['ukuran_tabel_kb'])->map(fn ($kb, $t) => "{$t} {$kb}")->implode(', ')."\n" : '')
            .'- Kriteria lulus: p95 < '.self::BATAS_P95_MS." ms dan tidak ada respons gagal\n\n"
            ."| Skenario | Ulang | Rata-rata (ms) | p95 (ms) | Maks (ms) | Query/request | Gagal | Hasil |\n"
            ."|---|---:|---:|---:|---:|---:|---:|---|\n";

        foreach ($this->hasil as $h) {
            $lulus = $h['p95_ms'] < self::BATAS_P95_MS && $h['gagal'] === 0 ? 'Lulus' : 'Tidak lulus';
            $md .= "| {$h['skenario']} | {$h['ulang']} | {$h['rata_ms']} | {$h['p95_ms']} | {$h['maks_ms']} | {$h['query_per_request']} | {$h['gagal']} | {$lulus} |\n";
        }

        $dir = storage_path('app/stress');
        @mkdir($dir, 0775, true);
        $nama = 'laporan-'.now()->format('Ymd-His');
        file_put_contents("{$dir}/{$nama}.md", $md);
        file_put_contents("{$dir}/{$nama}.json", json_encode(['meta' => $meta, 'hasil' => $this->hasil], JSON_PRETTY_PRINT));

        fwrite(STDERR, "\n".$md."\nLaporan: {$dir}/{$nama}.md\n");
    }
}
