<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Unit;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Pinjaman aktif milik anggota yang login.
        $loans = $user->activeLoans()
            ->with('unit')
            ->orderBy('due_date')
            ->get();

        // Histori peminjaman yang sudah selesai milik anggota yang login.
        $history = $user->loans()
            ->where('status', Loan::STATUS_SELESAI)
            ->with('unit')
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        return view('anggota.pinjaman', compact('loans', 'history'));
    }

    public function store(Request $request, Unit $unit, LoanService $service): RedirectResponse
    {
        try {
            $loan = $service->pinjam($request->user(), $unit);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Peminjaman berhasil. Jatuh tempo: '
            .$loan->due_date->translatedFormat('d F Y').'.'); // MSG-15
    }

    public function ajukanKembali(Request $request, Loan $loan, LoanService $service): RedirectResponse
    {
        try {
            $service->ajukanPengembalian($loan, $request->user());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Permintaan pengembalian unit berhasil diajukan ke Admin. Menunggu persetujuan.');
    }
}
