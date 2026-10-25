@php
    $messages = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($type) => session()->has($type))
        ->map(fn ($type) => [$type, session($type)]);
    $icons = ['success' => 'check-circle-2', 'error' => 'alert-circle', 'warning' => 'alert-triangle', 'info' => 'info'];
@endphp
@if ($messages->isNotEmpty())
    <div class="toast-container" aria-live="polite">
        @foreach ($messages as [$type, $message])
            <div class="toast-item {{ $type }}">
                <i data-lucide="{{ $icons[$type] }}" width="18" height="18"></i>
                <div style="flex:1">{{ $message }}</div>
                <button type="button" data-dismiss-toast title="Tutup Notifikasi"><i data-lucide="x" width="14" height="14"></i></button>
            </div>
        @endforeach
    </div>
@endif
