<x-layouts.app title="Katalog Unit & Perbekalan">
    <div class="page-stack">
        <x-page-header title="Katalog Unit"
                       :description="$bisaPinjam ? 'Cari unit dan ajukan peminjaman. Sisa kuota pinjaman Anda: '.max(0, $sisaKuota).' unit.' : 'Mode lihat: Admin tidak dapat meminjam unit.'" />

        <form method="GET" class="filter-bar">
            <div class="filter-group-left" style="display:flex; gap:10px; flex-wrap:wrap; flex:1">
                <div class="search-container" style="flex:1; min-width:220px; max-width:420px">
                    <i data-lucide="search" width="16" height="16" class="search-icon"></i>
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="input-control search-input" placeholder="Cari nama unit...">
                </div>
                <select name="kategori" class="input-control" style="width:auto; min-width:170px">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters['kategori'] === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="input-control" style="width:auto; min-width:150px">
                    <option value="">Semua status</option>
                    <option value="tersedia" @selected($filters['status'] === 'tersedia')>Tersedia</option>
                    <option value="dipinjam" @selected($filters['status'] === 'dipinjam')>Dipinjam</option>
                </select>
            </div>
            <div class="filter-group-right" style="display:flex; gap:8px">
                <button type="submit" class="btn-primary">Cari</button>
                <a href="{{ route('katalog') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-container">
            @if ($units->isEmpty())
                <x-empty title="Unit tidak ditemukan." /> {{-- MSG-20 --}}
            @else
                <table class="tactical-table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Unit</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        @if ($bisaPinjam)<th style="text-align:right">Aksi</th>@endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td class="text-mono">{{ $unit->unit_code }}</td>
                            <td>
                                <div>{{ $unit->name }}</div>
                                @if ($unit->description)<div class="text-meta" style="font-size:12px">{{ \Illuminate\Support\Str::limit($unit->description, 80) }}</div>@endif
                            </td>
                            <td>
                                <div class="unit-card-categories">
                                    @foreach ($unit->categories as $category)
                                        <span class="unit-cat-pill">{{ $category->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td><x-badge :status="ucfirst($unit->status)" /></td>
                            @if ($bisaPinjam)
                                <td style="text-align:right">
                                    @php
                                        $bolehPinjam = $unit->isTersedia() && $sisaKuota > 0;
                                        $tenggat = today()->addDays(config('gudang.lama_pinjam_hari'));
                                    @endphp
                                    <button type="button" class="btn-primary btn-sm"
                                            @disabled(! $bolehPinjam)
                                            @if ($bolehPinjam) data-modal-open="pinjam-{{ $unit->id }}" @endif>
                                        <i data-lucide="package-plus" width="14" height="14"></i> Pinjam
                                    </button>

                                    @if ($bolehPinjam)
                                        <x-confirm id="pinjam-{{ $unit->id }}" title="Konfirmasi Peminjaman Unit" confirmLabel="Konfirmasi Pinjam"
                                                   :action="route('pinjam', $unit)">
                                            <div style="display:flex; flex-direction:column; gap:10px">
                                                <div>Anda akan mengajukan peminjaman unit berikut:</div>
                                                <div style="background:rgba(0,0,0,0.25); padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; flex-direction:column; gap:6px; font-size:13px">
                                                    <div>Nama Unit: <strong style="color:var(--text-main, #E5E7EB)">{{ $unit->name }}</strong></div>
                                                    <div>Kode Unit: <strong class="text-mono" style="color:var(--color-accent, #EAB308)">{{ $unit->unit_code }}</strong></div>
                                                    <div>Kategori: <strong>{{ $unit->categories->pluck('name')->join(', ') ?: '-' }}</strong></div>
                                                    <div>Waktu Tenggat: <strong style="color:#4ADE80">{{ tanggal($tenggat) }}</strong> <span style="font-size:11.5px; color:var(--text-muted)">({{ config('gudang.lama_pinjam_hari') }} hari)</span></div>
                                                </div>
                                                <div style="font-size:12px; color:var(--text-muted); line-height:1.4">
                                                    Pastikan unit dikembalikan sebelum batas waktu tenggat guna menghindari denda keterlambatan ({{ rupiah(config('gudang.denda_per_hari')) }}/hari).
                                                </div>
                                            </div>
                                        </x-confirm>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <x-pagination :paginator="$units" />
    </div>
</x-layouts.app>
