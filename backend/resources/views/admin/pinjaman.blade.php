<x-layouts.app title="Pusat Peminjaman & Pengembalian">
    <div class="page-stack">
        <x-page-header title="Pinjaman Aktif"
                       description="Semua unit yang sedang dipinjam. Tarif denda {{ rupiah(config('gudang.denda_per_hari')) }} per hari keterlambatan." />

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
                                <div>{{ $loan->user->name }}</div>
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
                                <button type="button" class="btn-primary btn-sm" data-modal-open="kembali-{{ $loan->id }}">
                                    <i data-lucide="undo-2" width="14" height="14"></i> Proses Pengembalian
                                </button>
                                <x-confirm id="kembali-{{ $loan->id }}" title="Konfirmasi Pengembalian" confirmLabel="Konfirmasi"
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
        <x-pagination :paginator="$loans" />
    </div>
</x-layouts.app>
