<x-layouts.app title="Pusat Peminjaman & Pengembalian">
    <div class="page-stack">
        <x-page-header title="Pinjaman Aktif"
                       description="Semua unit yang sedang dipinjam. Tarif denda {{ rupiah(config('gudang.denda_per_hari')) }} per hari keterlambatan." />

        @php $permintaanAda = $loans->contains(fn ($l) => $l->isMenungguPersetujuan()); @endphp
        @if ($permintaanAda)
            <div class="alert-box warning" style="border-left: 4px solid var(--color-warning);">
                <i data-lucide="bell-ring" width="20" height="20" style="color:var(--color-warning)"></i>
                <span>Terdapat permohonan pengembalian unit dari survivor yang menunggu verifikasi dan persetujuan Komandan.</span>
            </div>
        @endif

        <div class="table-container">
            @if ($loans->isEmpty())
                <x-empty title="Tidak ada pinjaman aktif." />
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
                    @foreach ($loans as $loan)
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
                                        <button type="button" class="btn-primary btn-sm" data-modal-open="setuju-{{ $loan->id }}">
                                            <i data-lucide="check" width="14" height="14"></i> Setuju
                                        </button>
                                        <button type="button" class="btn-danger btn-sm" data-modal-open="tolak-{{ $loan->id }}">
                                            <i data-lucide="x" width="14" height="14"></i> Tolak
                                        </button>
                                    </div>
                                    <x-confirm id="setuju-{{ $loan->id }}" title="Setujui Pengembalian Unit" confirmLabel="Setuju & Terima Unit"
                                               :action="route('pinjaman.kembali', $loan)">
                                        <div>Unit: <strong>{{ $loan->unit->unit_code }} – {{ $loan->unit->name }}</strong></div>
                                        <div>Peminjam: <strong>{{ $loan->user->name }}</strong></div>
                                        <div>Hari terlambat: <strong>{{ $hari }} hari</strong></div>
                                        <div>Denda: <strong>{{ rupiah($denda) }}</strong></div>
                                        <div style="font-size:12px; color:var(--text-muted); margin-top:8px">
                                            Dengan menyetujui, unit diverifikasi telah diterima kembali di pos komando dan status unit kembali menjadi Tersedia.
                                        </div>
                                    </x-confirm>
                                    <x-confirm id="tolak-{{ $loan->id }}" title="Tolak Permintaan Pengembalian" variant="danger" confirmLabel="Tolak Pengembalian"
                                               :action="route('pinjaman.tolak', $loan)">
                                        <div>Tolak permohonan pengembalian dari <strong>{{ $loan->user->name }}</strong> untuk unit <strong>{{ $loan->unit->name }}</strong>?</div>
                                        <div style="font-size:12px; color:var(--text-secondary); margin-top:6px">Status pinjaman akan tetap Aktif pada survivor.</div>
                                        <div class="form-group" style="margin-top:12px">
                                            <label for="note-{{ $loan->id }}" class="form-label"><span>Alasan Penolakan</span></label>
                                            <input type="text" id="note-{{ $loan->id }}" name="note" class="input-control" placeholder="Contoh: Unit fisik belum diserahkan ke pos gudang">
                                        </div>
                                    </x-confirm>
                                @else
                                    <button type="button" class="btn-secondary btn-sm" data-modal-open="kembali-{{ $loan->id }}">
                                        <i data-lucide="undo-2" width="14" height="14"></i> Pengembalian
                                    </button>
                                    <x-confirm id="kembali-{{ $loan->id }}" title="Konfirmasi Pengembalian" confirmLabel="Konfirmasi"
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
        <x-pagination :paginator="$loans" />
    </div>
</x-layouts.app>
