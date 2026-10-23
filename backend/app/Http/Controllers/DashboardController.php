<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('admin.dashboard', [
                'stats' => [
                    'total' => Unit::count(),
                    'tersedia' => Unit::tersedia()->count(),
                    'dipinjam' => Unit::where('status', Unit::STATUS_DIPINJAM)->count(),
                    'terlambat' => Loan::terlambat()->count(),
                    'anggota' => User::anggota()->count(),
                ],
                'terlambat' => Loan::terlambat()->with(['user', 'unit'])->orderBy('due_date')->limit(10)->get(),
            ]);
        }

        // Anggota hanya melihat data miliknya sendiri.
        $loans = $user->activeLoans()->with('unit')->orderBy('due_date')->get();

        return view('anggota.dashboard', [
            'loans' => $loans,
            'maks' => config('gudang.maks_pinjaman_aktif'),
            'adaTerlambat' => $loans->contains(fn ($loan) => $loan->isTerlambat()),
            'jatuhTempoTerdekat' => $loans->first()?->due_date,
        ]);
    }
}
