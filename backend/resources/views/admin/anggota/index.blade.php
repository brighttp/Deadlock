<x-layouts.app title="Direktori Personel Survivor">
    <div class="page-stack">
        <x-page-header title="Anggota" description="Kelola akun anggota (survivor). Akun anggota hanya dibuat oleh Admin.">
            <x-slot:actions>
                <a href="{{ route('anggota.create') }}" class="btn-primary"><i data-lucide="user-plus" width="16" height="16"></i> Tambah Anggota</a>
            </x-slot:actions>
        </x-page-header>

        <form method="GET" class="filter-bar">
            <div class="search-container" style="flex:1; max-width:420px">
                <i data-lucide="search" width="16" height="16" class="search-icon"></i>
                <input type="text" name="q" value="{{ $q }}" class="input-control search-input" placeholder="Cari nama atau email...">
            </div>
            <button type="submit" class="btn-secondary">Cari</button>
        </form>

        <div class="table-container">
            @if ($members->isEmpty())
                <x-empty title="Anggota tidak ditemukan." />
            @else
                <table class="tactical-table">
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor HP</th>
                        <th style="text-align:center">Pinjaman Aktif</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td class="text-mono">{{ $member->profile?->phone ?? '-' }}</td>
                            <td style="text-align:center">{{ $member->active_loans_count }} / {{ config('gudang.maks_pinjaman_aktif') }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('anggota.edit', $member) }}" class="btn-secondary btn-sm"><i data-lucide="pencil" width="14" height="14"></i> Ubah</a>
                                    <button type="button" class="btn-danger btn-sm" data-modal-open="hapus-{{ $member->id }}"><i data-lucide="trash-2" width="14" height="14"></i> Hapus</button>
                                </div>
                                <x-confirm id="hapus-{{ $member->id }}" title="Hapus Anggota" variant="danger" confirmLabel="Hapus"
                                           :action="route('anggota.destroy', $member)" method="DELETE">
                                    Hapus anggota <strong>{{ $member->name }}</strong>? Riwayat peminjamannya tetap tersimpan.
                                </x-confirm>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <x-pagination :paginator="$members" />
    </div>
</x-layouts.app>
