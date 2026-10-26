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
            $loan->return_status = null;
            $loan->return_rejection_note = null;
            $loan->save();

            // withTrashed: jaga-jaga, walau unit dipinjam tidak bisa dihapus.
            Unit::withTrashed()->whereKey($loan->unit_id)->update(['status' => Unit::STATUS_TERSEDIA]);

            return $loan;
        });
    }

    public function ajukanPengembalian(Loan $loan, User $user): Loan
    {
        return DB::transaction(function () use ($loan, $user) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'kembalikan' => 'Anda tidak berhak mengajukan pengembalian untuk pinjaman ini.',
                ]);
            }

            if ($loan->status !== Loan::STATUS_AKTIF) {
                throw ValidationException::withMessages([
                    'kembalikan' => 'Pinjaman ini sudah selesai / dikembalikan.',
                ]);
            }

            if ($loan->isMenungguPersetujuan()) {
                throw ValidationException::withMessages([
                    'kembalikan' => 'Pengembalian unit ini sudah diajukan dan sedang menunggu persetujuan Admin.',
                ]);
            }

            $loan->return_status = Loan::RETURN_DIAJUKAN;
            $loan->return_requested_at = now();
            $loan->return_rejection_note = null;
            $loan->save();

            return $loan;
        });
    }

    public function tolakPengembalian(Loan $loan, ?string $note = null): Loan
    {
        return DB::transaction(function () use ($loan, $note) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== Loan::STATUS_AKTIF) {
                throw ValidationException::withMessages([
                    'tolak' => 'Pinjaman ini sudah tidak aktif.',
                ]);
            }

            if (! $loan->isMenungguPersetujuan()) {
                throw ValidationException::withMessages([
                    'tolak' => 'Pinjaman ini tidak sedang dalam status pengajuan pengembalian.',
                ]);
            }

            $loan->return_status = Loan::RETURN_DITOLAK;
            $loan->return_rejection_note = trim((string) $note) ?: 'Pengembalian belum dapat disetujui oleh Admin.';
            $loan->save();

            return $loan;
        });
    }
}
