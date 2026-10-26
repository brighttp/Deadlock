@props(['status'])
@php
    $class = match ($status) {
        'Tersedia' => 'badge-success',
        'Dipinjam', 'Menunggu Persetujuan' => 'badge-warning',
        'Aktif', 'Admin' => 'badge-accent',
        'Terlambat', 'Pengembalian Ditolak' => 'badge-danger',
        'Survivor' => 'badge-survivor',
        default => 'badge-neutral',
    };
@endphp
<span {{ $attributes->merge(['class' => "badge {$class}"]) }}>
    <span class="badge-dot"></span>
    <span>{{ $status }}</span>
</span>
