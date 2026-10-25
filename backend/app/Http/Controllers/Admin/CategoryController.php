<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('units')
            ->orderBy('name')
            ->paginate(config('gudang.per_halaman'));

        return view('admin.kategori.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.kategori.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('kategori.index')->with('success', 'Data berhasil disimpan.'); // MSG-19
    }

    public function edit(Category $category): View
    {
        return view('admin.kategori.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('kategori.index')->with('success', 'Data berhasil disimpan.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Cek dulu supaya pesan ramah, bukan error FK (pivot category_id restrict).
        if ($category->units()->exists()) {
            return back()->with('error', 'Kategori masih dipakai unit dan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($category) {
            // Sisa baris pivot hanya milik unit yang sudah di-soft delete (tidak dihitung "memakai"),
            // tapi tetap memblokir FK restrict, jadi dilepas dulu.
            $category->units()->detach();
            $category->delete();
        });

        return redirect()->route('kategori.index')->with('success', 'Data berhasil dihapus.');
    }
}
