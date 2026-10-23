<x-layouts.app title="Rekam Riwayat Peminjaman Unit">
    <div class="page-stack">
        <x-page-header title="Riwayat Peminjaman" description="Filter riwayat per anggota dan rentang tanggal pinjam, lalu cetak atau unduh PDF." />

        <form method="GET" action="{{ route('riwayat.index') }}" novalidate class="form-card">
            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr))">
                <x-field label="Anggota" name="user_id">
                    <select id="user_id" name="user_id" class="input-control @error('user_id') input-error @enderror">
                        <option value="">Semua anggota</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected((int) ($filter['user_id'] ?? 0) === $member->id)>
                                {{ $member->name }}{{ $member->trashed() ? ' (dihapus)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Tanggal Dari" name="dari">
                    <input type="date" id="dari" name="dari" value="{{ $filter['dari'] ?? '' }}" class="input-control @error('dari') input-error @enderror">
                </x-field>
                <x-field label="Tanggal Sampai" name="sampai">
                    <input type="date" id="sampai" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="input-control @error('sampai') input-error @enderror">
                </x-field>
            </div>
            <div class="form-actions">
                <a href="{{ route('riwayat.index') }}" class="btn-secondary">Reset</a>
                <button type="submit" class="btn-primary"><i data-lucide="filter" width="16" height="16"></i> Tampilkan</button>
                <a href="{{ route('riwayat.cetak', $filter) }}" target="_blank" class="btn-secondary"><i data-lucide="printer" width="16" height="16"></i> Cetak</a>
                <a href="{{ route('riwayat.pdf', $filter) }}" class="btn-secondary"><i data-lucide="file-down" width="16" height="16"></i> Unduh PDF</a>
            </div>
        </form>

        <div class="table-container">
            @if ($loans->isEmpty())
                <x-empty title="Tidak ada riwayat pada rentang tanggal ini." />
            @else
                @include('admin.riwayat._tabel', ['tampilPeminjam' => empty($filter['user_id'])])
            @endif
        </div>
        <x-pagination :paginator="$loans" />
    </div>
</x-layouts.app>
