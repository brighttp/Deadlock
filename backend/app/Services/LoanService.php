<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logika pinjam & kembali. Semua tanggal dihitung di Laravel (Asia/Jakarta),
 * bukan CURRENT_DATE database, karena server Supabase memakai UTC.
 */
class LoanService
{
    public function pinjam(User $user, Unit $unit): Loan
    {
        return DB::transaction(function () use ($user, $unit) {
            // Kunci baris user dan unit supaya dua request bersamaan tidak lolos cek yang sama.
            User::whereKey($user->id)->lockForUpdate()->first();
            $unit = Unit::whereKey($unit->id)->lockForUpdate()->firstOrFail();

            if (! $unit->isTersedia()) {
                throw ValidationException::withMessages([
                    'pinjam' => 'Unit sedang dipinjam dan tidak dapat dipinjam.', // MSG-09
                ]);
            }

            $maks = config('gudang.maks_pinjaman_aktif');
            if ($user->activeLoans()->count() >= $maks) {
                throw ValidationException::withMessages([
                    'pinjam' => "Batas peminjaman tercapai. Maksimal {$maks} unit aktif.", // MSG-08
                ]);
            }

            $loanDate = today();
            $loan = Loan::create([
                'user_id' => $user->id,
                'unit_id' => $unit->id,
                'loan_date' => $loanDate,
                'due_date' => $loanDate->copy()->addDays(config('gudang.lama_pinjam_hari')),
                'status' => Loan::STATUS_AKTIF,
            ]);

            $unit->status = Unit::STATUS_DIPINJAM;
            $unit->save();

            return $loan;
        });
    }

    public function kembali(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== Loan::STATUS_AKTIF) {
                throw ValidationException::withMessages([
                    'kembali' => 'Pinjaman ini sudah dikembalikan.',
                ]);
            }

            $returnDate = today();
            $loan->return_date = $returnDate;
            $loan->fine_amount = $loan->hitungDenda($returnDate); // tepat di due_date => 0
            $loan->status = Loan::STATUS_SELESAI;
            $loan->save();

            // withTrashed: jaga-jaga, walau unit dipinjam tidak bisa dihapus.
            Unit::withTrashed()->whereKey($loan->unit_id)->update(['status' => Unit::STATUS_TERSEDIA]);

            return $loan;
        });
    }
}
