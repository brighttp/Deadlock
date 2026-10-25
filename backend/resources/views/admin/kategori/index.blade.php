<x-layouts.app title="Manajemen Kategori Logistik">
    <div class="page-stack">
        <x-page-header title="Kategori Logistik" description="Kelola kategori untuk mengelompokkan unit gudang.">
            <x-slot:actions>
                <a href="{{ route('kategori.create') }}" class="btn-primary"><i data-lucide="plus" width="16" height="16"></i> Tambah Kategori</a>
            </x-slot:actions>
        </x-page-header>

        <div class="table-container">
            @if ($categories->isEmpty())
                <x-empty title="Belum ada kategori." />
            @else
                <table class="tactical-table">
                    <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th style="text-align:center">Jumlah Unit</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td style="text-align:center">{{ $category->units_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('kategori.edit', $category) }}" class="btn-secondary btn-sm"><i data-lucide="pencil" width="14" height="14"></i> Ubah</a>
                                    <button type="button" class="btn-danger btn-sm" data-modal-open="hapus-{{ $category->id }}"><i data-lucide="trash-2" width="14" height="14"></i> Hapus</button>
                                </div>
                                <x-confirm id="hapus-{{ $category->id }}" title="Hapus Kategori" variant="danger" confirmLabel="Hapus"
                                           :action="route('kategori.destroy', $category)" method="DELETE">
                                    Hapus kategori <strong>{{ $category->name }}</strong>?
                                </x-confirm>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <x-pagination :paginator="$categories" />
    </div>
</x-layouts.app>
