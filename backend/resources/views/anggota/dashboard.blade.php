<x-layouts.app title="Dashboard Operasi Survivor">
    <div class="page-stack">
        <x-page-header title="Dashboard" description="Selamat datang, {{ auth()->user()->name }}.">
            <x-slot:actions>
                <a href="{{ route('katalog') }}" class="btn-primary"><i data-lucide="package-search" width="16" height="16"></i> Katalog</a>
                <a href="{{ route('pinjaman-saya') }}" class="btn-secondary"><i data-lucide="clipboard-list" width="16" height="16"></i> Pinjaman Saya</a>
            </x-slot:actions>
        </x-page-header>

        @if ($adaTerlambat)
            <div class="urgent-overdue-alert">
                <i data-lucide="alert-octagon" width="20" height="20"></i>
                <span>Peminjaman Anda melewati jatuh tempo. Hubungi Admin untuk pengembalian.</span> {{-- MSG-13 --}}
            </div>
        @endif

        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))">
            <div class="stat-card">
                <div class="stat-header"><span class="stat-label">Pinjaman Aktif</span><i data-lucide="clipboard-list" width="18" height="18"></i></div>
                <div class="stat-value">{{ $loans->count() }} dari {{ $maks }}</div>
                <div class="stat-meter-track"><div class="stat-meter-fill" style="width: {{ min(100, $loans->count() / $maks * 100) }}%"></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-label">Jatuh Tempo Terdekat</span><i data-lucide="calendar-clock" width="18" height="18"></i></div>
                <div class="stat-value" style="font-size:22px">{{ tanggal($jatuhTempoTerdekat) }}</div>
            </div>
        </div>

        <div class="alert-box info">
            <i data-lucide="info" width="18" height="18"></i>
            <span>Untuk mengembalikan unit, klik <strong>Kembalikan Unit</strong> untuk mengajukan permohonan ke Admin/Komandan.</span>
        </div>

        <div class="table-container">
            @if ($loans->isEmpty())
                <x-empty title="Belum ada pinjaman aktif." description="Pinjam unit lewat halaman Katalog." />
            @else
                <table class="tactical-table">
                    <thead><tr><th>Unit</th><th>Tanggal Pinjam</th><th>Jatuh Tempo</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
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
                            <td style="text-align:right">
                                @if ($loan->isMenungguPersetujuan())
                                    <span class="badge badge-warning" style="display:inline-flex; align-items:center; gap:5px; padding:6px 10px">
                                        <i data-lucide="clock" width="13" height="13"></i> Menunggu Persetujuan
                                    </span>
                                @else
                                    @if ($loan->isPengembalianDitolak())
                                        <div class="text-danger" style="font-size:11.5px; margin-bottom:4px; text-align:right" title="{{ $loan->return_rejection_note }}">
                                            <i data-lucide="alert-circle" width="12" height="12"></i> Ditolak
                                        </div>
                                    @endif
                                    <button type="button" class="btn-primary btn-sm" data-modal-open="kembalikan-dash-{{ $loan->id }}">
                                        <i data-lucide="undo-2" width="14" height="14"></i> Kembalikan Unit
                                    </button>
                                    <x-confirm id="kembalikan-dash-{{ $loan->id }}" title="Konfirmasi Pengembalian Unit" confirmLabel="Ajukan Pengembalian"
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
</x-layouts.app>
