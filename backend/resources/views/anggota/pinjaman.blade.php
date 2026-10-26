<x-layouts.app title="Daftar Pinjaman Logistik Saya">
    <div class="page-stack">
        <x-page-header title="Pinjaman Saya"
                       description="Pantau unit aktif yang sedang Anda pinjam serta histori peminjaman perbekalan Anda." />

        @if ($loans->contains(fn ($loan) => $loan->isTerlambat()))
            <div class="urgent-overdue-alert">
                <i data-lucide="alert-octagon" width="20" height="20"></i>
                <span>Peminjaman Anda melewati jatuh tempo. Hubungi Admin untuk pengembalian.</span> {{-- MSG-13 --}}
            </div>
        @endif

        {{-- Section 1: Pinjaman Aktif --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px">
                <h3 class="section-title" style="margin-bottom:0">Pinjaman Aktif</h3>
                <span class="text-meta" style="font-size:13px">Kuota terpakai: {{ $loans->count() }} dari {{ config('gudang.maks_pinjaman_aktif') }} unit</span>
            </div>

            @if ($loans->isNotEmpty())
                <div class="alert-box info" style="margin-bottom:12px">
                    <i data-lucide="info" width="18" height="18"></i>
                    <span>Untuk mengembalikan unit, klik <strong>Kembalikan Unit</strong> untuk mengajukan permohonan ke Admin/Komandan.</span>
                </div>
            @endif

            <div class="table-container">
                @if ($loans->isEmpty())
                    <x-empty title="Tidak ada pinjaman aktif." description="Pinjam unit lewat halaman Katalog." />
                @else
                    <table class="tactical-table">
                        <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Perkiraan Denda</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($loans as $loan)
                            <tr>
                                <td><span class="text-mono">{{ $loan->unit->unit_code }}</span> – {{ $loan->unit->name }}</td>
                                <td>{{ tanggal($loan->loan_date) }}</td>
                                <td>{{ tanggal($loan->due_date) }}</td>
                                <td>
                                    <x-badge :status="$loan->statusLabel()" />
                                    @if ($loan->isTerlambat())<div class="text-danger" style="font-size:12px">{{ $loan->hariTerlambat() }} hari</div>@endif
                                </td>
                                <td>{{ rupiah($loan->hitungDenda()) }}</td>
                                <td style="text-align:right">
                                    @if ($loan->isMenungguPersetujuan())
                                        <span class="badge badge-warning" style="display:inline-flex; align-items:center; gap:5px; padding:6px 10px">
                                            <i data-lucide="clock" width="13" height="13"></i> Menunggu Persetujuan
                                        </span>
                                    @else
                                        @if ($loan->isPengembalianDitolak())
                                            <div class="text-danger" style="font-size:11.5px; margin-bottom:4px; text-align:right" title="{{ $loan->return_rejection_note }}">
                                                <i data-lucide="alert-circle" width="12" height="12"></i> Ditolak: {{ \Illuminate\Support\Str::limit($loan->return_rejection_note, 25) }}
                                            </div>
                                        @endif
                                        <button type="button" class="btn-primary btn-sm" data-modal-open="kembalikan-{{ $loan->id }}">
                                            <i data-lucide="undo-2" width="14" height="14"></i> Kembalikan Unit
                                        </button>
                                        <x-confirm id="kembalikan-{{ $loan->id }}" title="Konfirmasi Pengembalian Unit" confirmLabel="Ajukan Pengembalian"
                                                   :action="route('pinjaman-saya.kembalikan', $loan)">
                                            <div style="display:flex; flex-direction:column; gap:10px">
                                                <div>Anda akan mengajukan pengembalian unit perbekalan ke Admin/Komandan:</div>
                                                <div style="background:rgba(0,0,0,0.25); padding:10px 12px; border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; flex-direction:column; gap:6px; font-size:13px">
                                                    <div>Unit: <strong>{{ $loan->unit->unit_code }} – {{ $loan->unit->name }}</strong></div>
                                                    <div>Tanggal Pinjam: <strong>{{ tanggal($loan->loan_date) }}</strong></div>
                                                    <div>Jatuh Tempo: <strong>{{ tanggal($loan->due_date) }}</strong></div>
                                                    @if ($loan->hariTerlambat() > 0)
                                                        <div>Terlambat: <strong class="text-danger">{{ $loan->hariTerlambat() }} hari</strong></div>
                                                        <div>Perkiraan Denda: <strong class="text-danger">{{ rupiah($loan->hitungDenda()) }}</strong></div>
                                                    @endif
                                                </div>
                                                <div style="font-size:12px; color:var(--text-muted)">
                                                    Setelah diajukan, serahkan unit fisik ke pos komando logistik untuk diverifikasi dan disetujui oleh Admin.
                                                </div>
                                            </div>
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

        {{-- Section 2: Histori Peminjaman Selesai --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px">
                <h3 class="section-title" style="margin-bottom:0">Histori Peminjaman</h3>
                <span class="text-meta" style="font-size:13px">Total riwayat: {{ $history->count() }} transaksi</span>
            </div>

            <div class="table-container">
                @if ($history->isEmpty())
                    <x-empty title="Belum ada histori peminjaman." description="Daftar unit yang telah selesai Anda pinjam dan dikembalikan akan tercatat di sini." />
                @else
                    <table class="tactical-table">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Unit</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th style="text-align:right">Denda Dibayar</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($history as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="text-mono">{{ $item->unit->unit_code }}</span> – {{ $item->unit->name }}</td>
                                <td>{{ tanggal($item->loan_date) }}</td>
                                <td>{{ tanggal($item->due_date) }}</td>
                                <td>{{ tanggal($item->return_date) }}</td>
                                <td style="text-align:right">{{ rupiah($item->fine_amount) }}</td>
                                <td><x-badge status="Selesai" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
