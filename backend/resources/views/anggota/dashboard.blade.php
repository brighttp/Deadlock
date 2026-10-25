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
            <span>Untuk mengembalikan unit, hubungi Admin terlebih dahulu.</span> {{-- MSG-14 --}}
        </div>

        <div class="table-container">
            @if ($loans->isEmpty())
                <x-empty title="Belum ada pinjaman aktif." description="Pinjam unit lewat halaman Katalog." />
            @else
                <table class="tactical-table">
                    <thead><tr><th>Unit</th><th>Tanggal Pinjam</th><th>Jatuh Tempo</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($loans as $loan)
                        <tr>
                            <td><span class="text-mono">{{ $loan->unit->unit_code }}</span> – {{ $loan->unit->name }}</td>
                            <td>{{ tanggal($loan->loan_date) }}</td>
                            <td>{{ tanggal($loan->due_date) }}</td>
                            <td><x-badge :status="$loan->statusLabel()" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
