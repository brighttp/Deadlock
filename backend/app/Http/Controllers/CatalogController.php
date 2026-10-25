<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q')),
            'kategori' => $request->integer('kategori') ?: null,
            'status' => in_array($request->query('status'), [Unit::STATUS_TERSEDIA, Unit::STATUS_DIPINJAM], true)
                ? $request->query('status') : null,
        ];

        // whereLike => ILIKE di Postgres (case-insensitive).
        $units = Unit::with('categories')
            ->when($filters['q'] !== '', fn ($q) => $q->whereLike('name', "%{$filters['q']}%"))
            ->when($filters['kategori'], fn ($q, $id) => $q->whereHas('categories', fn ($c) => $c->whereKey($id)))
            ->when($filters['status'], fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->orderBy('unit_code')
            ->paginate(config('gudang.per_halaman'))
            ->withQueryString();

        $user = $request->user();

        return view('katalog', [
            'units' => $units,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(),
            'bisaPinjam' => $user->isAnggota(),
            'sisaKuota' => $user->isAnggota()
                ? config('gudang.maks_pinjaman_aktif') - $user->activeLoans()->count()
                : 0,
        ]);
    }
}
