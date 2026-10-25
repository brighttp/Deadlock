<x-layouts.app title="Daftar Pinjaman Logistik Saya">
    <div class="page-stack">
        <x-page-header title="Pinjaman Saya"
                       description="Unit yang sedang Anda pinjam ({{ $loans->count() }} dari {{ config('gudang.maks_pinjaman_aktif') }})." />

        @if ($loans->contains(fn ($loan) => $loan->isTerlambat()))
            <div class="urgent-overdue-alert">
                <i data-lucide="alert-octagon" width="20" height="20"></i>
                <span>Peminjaman Anda melewati jatuh tempo. Hubungi Admin untuk pengembalian.</span> {{-- MSG-13 --}}
            </div>
        @endif

        <div class="alert-box info">
            <i data-lucide="info" width="18" height="18"></i>
            <span>Untuk mengembalikan unit, hubungi Admin terlebih dahulu.</span> {{-- MSG-14 --}}
        </div>

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
                        <th style="text-align:right">Perkiraan Denda</th>
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
                            <td style="text-align:right">{{ rupiah($loan->hitungDenda()) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
