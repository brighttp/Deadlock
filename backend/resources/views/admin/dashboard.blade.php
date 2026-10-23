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

        <div>
            <h3 class="section-title" style="margin-bottom:10px">Peminjaman Terlambat</h3>
            <div class="table-container">
                @if ($terlambat->isEmpty())
                    <x-empty title="Tidak ada peminjaman terlambat." />
                @else
                    <table class="tactical-table">
                        <thead>
                        <tr><th>Peminjam</th><th>Unit</th><th>Jatuh Tempo</th><th>Terlambat</th><th style="text-align:right">Denda Berjalan</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($terlambat as $loan)
                            <tr>
                                <td>{{ $loan->user->name }}</td>
                                <td><span class="text-mono">{{ $loan->unit->unit_code }}</span> – {{ $loan->unit->name }}</td>
                                <td>{{ tanggal($loan->due_date) }}</td>
                                <td class="text-danger">{{ $loan->hariTerlambat() }} hari</td>
                                <td style="text-align:right">{{ rupiah($loan->hitungDenda()) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
