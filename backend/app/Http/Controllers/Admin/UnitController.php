<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $units = Unit::with('categories')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->whereLike('name', "%{$q}%")
                ->orWhereLike('unit_code', "%{$q}%")))
            ->orderBy('unit_code')
            ->paginate(config('gudang.per_halaman'))
            ->withQueryString();

        return view('admin.unit.index', compact('units', 'q'));
    }

    public function create(): View
    {
        return view('admin.unit.form', [
            'unit' => new Unit,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $unit = Unit::create($request->safe()->except('categories'));
            $unit->categories()->sync($request->validated('categories'));
        });

        return redirect()->route('unit.index')->with('success', 'Data berhasil disimpan.'); // MSG-19
    }

    public function edit(Unit $unit): View
    {
        return view('admin.unit.form', [
            'unit' => $unit->load('categories'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        DB::transaction(function () use ($request, $unit) {
            $unit->update($request->safe()->except('categories'));
            $unit->categories()->sync($request->validated('categories'));
        });

        return redirect()->route('unit.index')->with('success', 'Data berhasil disimpan.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if (! $unit->isTersedia()) {
            return back()->with('error', 'Unit sedang dipinjam dan tidak dapat dihapus.'); // MSG-10
        }

        $unit->delete(); // soft delete: riwayat pinjaman tetap utuh

        return redirect()->route('unit.index')->with('success', 'Data berhasil dihapus.');
    }
}
