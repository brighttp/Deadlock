<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HistoryFilterRequest;
use App\Models\Loan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(HistoryFilterRequest $request): View
    {
        $filter = $request->validated();

        return view('admin.riwayat.index', [
            'filter' => $filter,
            'members' => User::withTrashed()->anggota()->orderBy('name')->get(),
            'loans' => $this->query($filter)->paginate(config('gudang.per_halaman'))->withQueryString(),
        ]);
    }

    // Cetak dan PDF memuat data sesuai filter aktif (tanpa paginasi).
    public function cetak(HistoryFilterRequest $request): View
    {
        return view('admin.riwayat.cetak', $this->dataCetak($request->validated()));
    }

    public function pdf(HistoryFilterRequest $request): Response
    {
        $data = $this->dataCetak($request->validated());

        return Pdf::loadView('admin.riwayat.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('riwayat-peminjaman-'.now()->format('Ymd-His').'.pdf');
    }

    private function query(array $filter): Builder
    {
        return Loan::with(['user', 'unit'])
            ->when($filter['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($filter['dari'] ?? null, fn ($q, $dari) => $q->whereDate('loan_date', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn ($q, $sampai) => $q->whereDate('loan_date', '<=', $sampai))
            ->orderByDesc('loan_date')
            ->orderByDesc('id');
    }

    private function dataCetak(array $filter): array
    {
        $loans = $this->query($filter)->get();

        return [
            'filter' => $filter,
            'loans' => $loans,
            'anggota' => isset($filter['user_id']) ? User::withTrashed()->find($filter['user_id']) : null,
            'totalDenda' => $loans->sum('fine_amount'),
        ];
    }
}
