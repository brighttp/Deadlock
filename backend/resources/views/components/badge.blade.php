@props(['status'])
@php
    $class = match ($status) {
        'Tersedia' => 'badge-success',
        'Dipinjam' => 'badge-warning',
        'Aktif', 'Admin' => 'badge-accent',
        'Terlambat' => 'badge-danger',
        'Survivor' => 'badge-survivor',
        default => 'badge-neutral',
    };
@endphp
<span {{ $attributes->merge(['class' => "badge {$class}"]) }}>
    <span class="badge-dot"></span>
    <span>{{ $status }}</span>
</span>
