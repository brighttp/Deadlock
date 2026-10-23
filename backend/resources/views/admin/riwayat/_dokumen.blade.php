{{-- Isi dokumen cetak/PDF: CSS sederhana (dompdf tidak mendukung CSS variable). --}}
<style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
    h1 { font-size: 16px; margin: 0 0 4px; }
    .meta { margin-bottom: 12px; color: #444; }
    .meta td { padding: 1px 10px 1px 0; }
    table.tactical-table { width: 100%; border-collapse: collapse; }
    table.tactical-table th, table.tactical-table td { border: 1px solid #999; padding: 5px 6px; text-align: left; }
    table.tactical-table th { background: #e6ebe0; }
    .total { margin-top: 10px; text-align: right; font-weight: bold; }
    .kosong { padding: 20px; text-align: center; color: #666; border: 1px dashed #999; }
</style>

<h1>Riwayat Peminjaman Unit – Gudang Deadlock</h1>
<table class="meta">
    <tr><td>Anggota</td><td>: {{ $anggota?->name ?? 'Semua anggota' }}{{ $anggota?->trashed() ? ' (dihapus)' : '' }}</td></tr>
    <tr><td>Rentang tanggal pinjam</td><td>: {{ isset($filter['dari']) ? tanggal(\Illuminate\Support\Carbon::parse($filter['dari'])) : 'awal' }} s.d. {{ isset($filter['sampai']) ? tanggal(\Illuminate\Support\Carbon::parse($filter['sampai'])) : 'sekarang' }}</td></tr>
    <tr><td>Dicetak</td><td>: {{ now()->translatedFormat('d F Y H:i') }} WIB</td></tr>
</table>

@if ($loans->isEmpty())
    <div class="kosong">Tidak ada riwayat pada rentang tanggal ini.</div>
@else
    @include('admin.riwayat._tabel', ['tampilPeminjam' => ! $anggota])
    <div class="total">Total denda: {{ rupiah($totalDenda) }} ({{ $loans->count() }} peminjaman)</div>
@endif
