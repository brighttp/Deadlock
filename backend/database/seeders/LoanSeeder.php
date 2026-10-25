<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        // Contoh pinjaman hanya dibuat sekali agar seeder aman dijalankan ulang.
        if (Loan::exists()) {
            return;
        }

        $user = User::pluck('id', 'email');
        $unit = Unit::pluck('id', 'unit_code');
        $lama = config('gudang.lama_pinjam_hari');

        // [email, kode unit, loan_date (hari lalu), return_date (hari lalu) atau null jika masih aktif]
        $contoh = [
            ['sarah@haven.org', 'UNIT-001', 2, null],   // aktif, belum jatuh tempo
            ['sarah@haven.org', 'UNIT-006', 1, null],   // aktif ke-2: Sarah sudah di batas maksimal
            ['joel@haven.org', 'UNIT-003', 8, null],    // aktif, terlambat 3 hari
            ['ellie@haven.org', 'UNIT-002', 20, 15],    // selesai tepat di due_date, denda 0
            ['glenn@haven.org', 'UNIT-009', 15, 7],     // selesai terlambat 3 hari, kena denda
        ];

        DB::transaction(function () use ($contoh, $user, $unit, $lama) {
            foreach ($contoh as [$email, $kode, $pinjamLalu, $kembaliLalu]) {
                $loanDate = today()->subDays($pinjamLalu);
                $returnDate = $kembaliLalu === null ? null : today()->subDays($kembaliLalu);

                $loan = new Loan([
                    'user_id' => $user[$email],
                    'unit_id' => $unit[$kode],
                    'loan_date' => $loanDate,
                    'due_date' => $loanDate->copy()->addDays($lama),
                    'return_date' => $returnDate,
                    'status' => $returnDate ? Loan::STATUS_SELESAI : Loan::STATUS_AKTIF,
                ]);
                $loan->fine_amount = $returnDate ? $loan->hitungDenda($returnDate) : 0;
                $loan->save();

                if (! $returnDate) {
                    Unit::whereKey($unit[$kode])->update(['status' => Unit::STATUS_DIPINJAM]);
                }
            }
        });
    }
}
