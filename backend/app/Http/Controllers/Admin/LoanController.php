<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(): View
    {
        // Semua pinjaman aktif; relasi withTrashed sudah diatur di model Loan.
        $loans = Loan::aktif()
            ->with(['user', 'unit'])
            ->orderBy('due_date')
            ->paginate(config('gudang.per_halaman'));

        return view('admin.pinjaman', compact('loans'));
    }

    public function kembali(Loan $loan, LoanService $service): RedirectResponse
    {
        try {
            $loan = $service->kembali($loan);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Pengembalian diproses. Denda: '.uang($loan->fine_amount).'.'); // MSG-16
    }
}
