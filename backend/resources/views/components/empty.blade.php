@props(['title' => 'Tidak ada data ditemukan', 'description' => null])
<div class="empty-state">
    <div class="empty-icon"><i data-lucide="package-open" width="24" height="24"></i></div>
    <div class="empty-title">{{ $title }}</div>
    @if ($description)<div class="empty-desc">{{ $description }}</div>@endif
</div>
