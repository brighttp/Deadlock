<x-layouts.app title="Inventaris Unit & Persenjataan">
    <div class="page-stack">
        <x-page-header title="Unit Inventaris" description="Kelola data unit senjata dan perlengkapan survival.">
            <x-slot:actions>
                <a href="{{ route('unit.create') }}" class="btn-primary"><i data-lucide="plus" width="16" height="16"></i> Tambah Unit</a>
            </x-slot:actions>
        </x-page-header>

        <form method="GET" class="filter-bar">
            <div class="search-container" style="flex:1; max-width:420px">
                <i data-lucide="search" width="16" height="16" class="search-icon"></i>
                <input type="text" name="q" value="{{ $q }}" class="input-control search-input" placeholder="Cari nama atau kode unit...">
            </div>
            <button type="submit" class="btn-secondary">Cari</button>
        </form>

        <div class="table-container">
            @if ($units->isEmpty())
                <x-empty title="Unit tidak ditemukan." />
            @else
                <table class="tactical-table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Unit</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td class="text-mono">{{ $unit->unit_code }}</td>
                            <td>{{ $unit->name }}</td>
                            <td>
                                <div class="unit-card-categories">
                                    @foreach ($unit->categories as $category)
                                        <span class="unit-cat-pill">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td><x-badge :status="ucfirst($unit->status)" /></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('unit.edit', $unit) }}" class="btn-secondary btn-sm"><i data-lucide="pencil" width="14" height="14"></i> Ubah</a>
                                    <button type="button" class="btn-danger btn-sm" data-modal-open="hapus-{{ $unit->id }}"><i data-lucide="trash-2" width="14" height="14"></i> Hapus</button>
                                </div>
                                <x-confirm id="hapus-{{ $unit->id }}" title="Hapus Unit" variant="danger" confirmLabel="Hapus"
                                           :action="route('unit.destroy', $unit)" method="DELETE">
                                    Hapus unit <strong>{{ $unit->unit_code }} – {{ $unit->name }}</strong>? Riwayat peminjaman unit ini tetap tersimpan.
                                </x-confirm>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <x-pagination :paginator="$units" />
    </div>
</x-layouts.app>
