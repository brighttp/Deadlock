{{-- Pembungkus field: label + slot input + pesan error di bawah field (ketentuan no. 10). --}}
@props(['label', 'name', 'required' => false, 'hint' => null])
@php $errorKey = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div {{ $attributes->merge(['class' => 'form-group']) }}>
    <label for="{{ $name }}" class="form-label">
        <span>{{ $label }}@if ($required)<span class="required-dot">*</span>@endif</span>
    </label>
    {{ $slot }}
    @error($errorKey)
        <span class="form-error">{{ $message }}</span>
    @else
        @if ($hint)<span class="form-hint">{{ $hint }}</span>@endif
    @enderror
</div>
