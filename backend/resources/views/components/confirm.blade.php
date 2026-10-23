{{-- Dialog konfirmasi yang mengirim form (hapus / proses pengembalian). Dibuka dengan data-modal-open="{id}". --}}
{{-- Di-push ke ujung <body> supaya position:fixed tidak terjebak di dalam container tabel. --}}
@props(['id', 'title', 'action', 'method' => 'POST', 'confirmLabel' => 'Konfirmasi', 'variant' => 'primary'])
@push('modals')
<div class="modal-overlay" id="{{ $id }}" role="dialog" aria-modal="true" hidden>
    <div class="modal-content" style="max-width: 440px">
        <div class="modal-header">
            <h3>{{ $title }}</h3>
            <button type="button" class="btn-icon" data-modal-close title="Tutup"><i data-lucide="x" width="18" height="18"></i></button>
        </div>
        <div class="modal-body">
            <div style="display:flex; gap:14px; align-items:flex-start">
                <div style="color: {{ $variant === 'danger' ? 'var(--color-danger)' : 'var(--color-accent)' }}; flex-shrink:0">
                    <i data-lucide="{{ $variant === 'danger' ? 'alert-triangle' : 'info' }}" width="24" height="24"></i>
                </div>
                <div style="font-size:13.5px; color:var(--text-secondary); line-height:1.5">{{ $slot }}</div>
            </div>
        </div>
        <form method="POST" action="{{ $action }}" class="modal-footer">
            @csrf
            @if (strtoupper($method) !== 'POST') @method($method) @endif
            <button type="button" class="btn-secondary btn-sm" data-modal-close>Batal</button>
            <button type="submit" class="{{ $variant === 'danger' ? 'btn-danger' : 'btn-primary' }} btn-sm">{{ $confirmLabel }}</button>
        </form>
    </div>
</div>
@endpush
