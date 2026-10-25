{{-- Tabel riwayat yang dipakai halaman, cetak, dan PDF. Butuh $loans dan $tampilPeminjam. --}}
<table class="tactical-table">
    <thead>
    <tr>
        <th>No</th>
        @if ($tampilPeminjam)<th>Peminjam</th>@endif
        <th>Unit</th>
        <th>Tanggal Pinjam</th>
        <th>Jatuh Tempo</th>
        <th>Tanggal Kembali</th>
        <th style="text-align:right">Denda</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($loans as $i => $loan)
        <tr>
            <td>{{ ($loans instanceof \Illuminate\Pagination\LengthAwarePaginator ? $loans->firstItem() : 1) + $i }}</td>
            @if ($tampilPeminjam)<td>{{ $loan->user->name }}@if ($loan->user->trashed()) <em>(dihapus)</em>@endif</td>@endif
            <td>{{ $loan->unit->unit_code }} – {{ $loan->unit->name }}</td>
            <td>{{ tanggal($loan->loan_date) }}</td>
            <td>{{ tanggal($loan->due_date) }}</td>
            <td>{{ tanggal($loan->return_date) }}</td>
            {{-- Pinjaman aktif: denda berjalan (belum final) sampai dikembalikan. --}}
            <td style="text-align:right">{{ rupiah($loan->status === 'selesai' ? $loan->fine_amount : $loan->hitungDenda()) }}</td>
            <td>{{ $loan->statusLabel() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
