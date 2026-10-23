@props(['title', 'description' => null])
<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">{{ $title }}</h1>
        @if ($description)<p class="page-header-desc">{{ $description }}</p>@endif
    </div>
    @isset($actions)
        <div class="page-header-actions">{{ $actions }}</div>
    @endisset
</div>
