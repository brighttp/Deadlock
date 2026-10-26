<x-layouts.app title="Dashboard Komando Logistik">
    <div class="page-stack">
        <x-page-header title="Dashboard Komando" description="Ringkasan kondisi gudang per {{ tanggal(today()) }}." />

        @php
            $cards = [
                ['Total Unit', $stats['total'], 'boxes', route('unit.index'), false],
                ['Tersedia', $stats['tersedia'], 'check-circle-2', route('katalog', ['status' => 'tersedia']), false],
                ['Dipinjam', $stats['dipinjam'], 'arrow-left-right', route('pinjaman.index'), false],
                ['Terlambat', $stats['terlambat'], 'alert-triangle', route('pinjaman.index'), true],
                ['Anggota', $stats['anggota'], 'users', route('anggota.index'), false],
            ];
        @endphp
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr))">
            @foreach ($cards as [$label, $value, $icon, $href, $danger])
                <a href="{{ $href }}" class="stat-card" style="text-decoration:none">
                    <div class="stat-header">
                        <span class="stat-label">{{ $label }}</span>
                        <i data-lucide="{{ $icon }}" width="18" height="18" style="color:var(--text-muted)"></i>
                    </div>
                    <div class="stat-value" style="color: {{ $danger && $value > 0 ? 'var(--color-danger)' : '#4ADE80' }}">{{ $value }}</div>
                </a>
            @endforeach
        </div>

        @php $adaPermintaan = $pinjamanAktif->contains(fn ($l) => $l->isMenungguPersetujuan()); @endphp
        @if ($adaPermintaan)
            <div class="alert-box warning" style="border-left: 4px solid var(--color-warning);">
                <i data-lucide="bell-ring" width="20" height="20" style="color:var(--color-warning)"></i>
                <span>Terdapat permohonan pengembalian unit dari survivor yang menunggu persetujuan Komandan.</span>
            </div>
        @endif

        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px">
                <h3 class="section-title" style="margin-bottom:0">Peminjaman Aktif</h3>
                @if ($stats['dipinjam'] > 0)
                    <a href="{{ route('pinjaman.index') }}" class="btn-secondary btn-sm" style="text-decoration:none">
                        Kelola di Peminjaman Unit <i data-lucide="arrow-right" width="14" height="14"></i>
                    </a>
                @endif
            </div>
            <div class="table-container">
                @if ($pinjamanAktif->isEmpty())
                    <x-empty title="Tidak ada peminjaman aktif saat ini." />
                @else
                    <table class="tactical-table">
                        <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Unit</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($pinjamanAktif as $loan)
                            @php $hari = $loan->hariTerlambat(); $denda = $loan->hitungDenda(); @endphp
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px">
                                        <span>{{ $loan->user->name }}</span>
                                        @if ($loan->isMenungguPersetujuan())
                                            <span class="badge badge-warning" style="font-size:11px" title="Survivor mengajukan pengembalian"><i data-lucide="bell" width="11" height="11"></i> Pengembalian Diajukan</span>
                                        @endif
                                    </div>
                                    <div class="text-meta" style="font-size:12px">{{ $loan->user->email }}</div>
                                </td>
                                <td><span class="text-mono">{{ $loan->unit->unit_code }}</span> – {{ $loan->unit->name }}</td>
                                <td>{{ tanggal($loan->loan_date) }}</td>
                                <td>{{ tanggal($loan->due_date) }}</td>
                                <td>
                                    <x-badge :status="$loan->statusLabel()" />
                                    @if ($hari > 0)<div class="text-danger" style="font-size:12px">{{ $hari }} hari</div>@endif
                                </td>
                                <td style="text-align:right">
                                    @if ($loan->isMenungguPersetujuan())
                                        <div class="row-actions" style="justify-content:flex-end">
                                            <button type="button" class="btn-primary btn-sm" data-modal-open="setuju-dash-{{ $loan->id }}">
                                                <i data-lucide="check" width="14" height="14"></i> Setuju
                                            </button>
                                            <button type="button" class="btn-danger btn-sm" data-modal-open="tolak-dash-{{ $loan->id }}">
                                                <i data-lucide="x" width="14" height="14"></i> Tolak
                                            </button>
                                        </div>
                                        <x-confirm id="setuju-dash-{{ $loan->id }}" title="Setujui Pengembalian Unit" confirmLabel="Setuju & Terima Unit"
                                                   :action="route('pinjaman.kembali', $loan)">
                                            <div>Unit: <strong>{{ $loan->unit->unit_code }} – {{ $loan->unit->name }}</strong></div>
                                            <div>Peminjam: <strong>{{ $loan->user->name }}</strong></div>
                                            <div>Hari terlambat: <strong>{{ $hari }} hari</strong></div>
                                            <div>Denda: <strong>{{ rupiah($denda) }}</strong></div>
                                            <div style="font-size:12px; color:var(--text-muted); margin-top:8px">
                                                Dengan menyetujui, unit diverifikasi telah diterima kembali di pos komando dan status unit kembali menjadi Tersedia.
                                            </div>
                                        </x-confirm>
                                        <x-confirm id="tolak-dash-{{ $loan->id }}" title="Tolak Permintaan Pengembalian" variant="danger" confirmLabel="Tolak Pengembalian"
                                                   :action="route('pinjaman.tolak', $loan)">
                                            <div>Tolak permohonan pengembalian dari <strong>{{ $loan->user->name }}</strong> untuk unit <strong>{{ $loan->unit->name }}</strong>?</div>
                                            <div class="form-group" style="margin-top:12px">
                                                <label for="note-dash-{{ $loan->id }}" class="form-label"><span>Alasan Penolakan</span></label>
                                                <input type="text" id="note-dash-{{ $loan->id }}" name="note" class="input-control" placeholder="Contoh: Unit fisik belum diserahkan ke pos gudang">
                                            </div>
                                        </x-confirm>
                                    @else
                                        <button type="button" class="btn-primary btn-sm" data-modal-open="kembali-aktif-{{ $loan->id }}">
                                            <i data-lucide="undo-2" width="14" height="14"></i> Pengembalian
                                        </button>
                                        <x-confirm id="kembali-aktif-{{ $loan->id }}" title="Konfirmasi Pengembalian" confirmLabel="Konfirmasi"
                                                   :action="route('pinjaman.kembali', $loan)">
                                            <div>Unit: <strong>{{ $loan->unit->unit_code }} – {{ $loan->unit->name }}</strong></div>
                                            <div>Peminjam: <strong>{{ $loan->user->name }}</strong></div>
                                            <div>Hari terlambat: <strong>{{ $hari }} hari</strong></div>
                                            <div>Denda: <strong>{{ rupiah($denda) }}</strong></div>
                                        </x-confirm>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div>
            <h3 class="section-title" style="margin-bottom:10px">Peminjaman Terlambat</h3>
            <div class="table-container">
                @if ($terlambat->isEmpty())
                    <x-empty title="Tidak ada peminjaman terlambat." />
                @else
                    <table class="tactical-table">
                        <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Unit</th>
                            <th>Jatuh Tempo</th>
                            <th>Terlambat</th>
                            <th style="text-align:right">Denda Berjalan</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($terlambat as $loan)
                            @php $hari = $loan->hariTerlambat(); $denda = $loan->hitungDenda(); @endphp
                            <tr>
                                <td>{{ $loan->user->name }}</td>
                                <td><span class="text-mono">{{ $loan->unit->unit_code }}</span> – {{ $loan->unit->name }}</td>
                                <td>{{ tanggal($loan->due_date) }}</td>
                                <td class="text-danger">{{ $hari }} hari</td>
                                <td style="text-align:right">{{ rupiah($denda) }}</td>
                                <td style="text-align:right">
                                    <button type="button" class="btn-primary btn-sm" data-modal-open="kembali-terlambat-{{ $loan->id }}">
                                        <i data-lucide="undo-2" width="14" height="14"></i> Pengembalian
                                    </button>
                                    <x-confirm id="kembali-terlambat-{{ $loan->id }}" title="Konfirmasi Pengembalian" confirmLabel="Konfirmasi"
                                               :action="route('pinjaman.kembali', $loan)">
                                        <div>Unit: <strong>{{ $loan->unit->unit_code }} – {{ $loan->unit->name }}</strong></div>
                                        <div>Peminjam: <strong>{{ $loan->user->name }}</strong></div>
                                        <div>Hari terlambat: <strong>{{ $hari }} hari</strong></div>
                                        <div>Denda: <strong>{{ rupiah($denda) }}</strong></div>
                                    </x-confirm>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
