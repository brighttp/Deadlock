<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
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
        // Hanya pinjaman aktif milik anggota yang login.
        $loans = $request->user()->activeLoans()
            ->with('unit')
            ->orderBy('due_date')
            ->get();

        return view('anggota.pinjaman', compact('loans'));
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
}
