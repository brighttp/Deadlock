@php $editing = $category->exists; @endphp
<x-layouts.app :title="$editing ? 'Ubah Kategori' : 'Tambah Kategori'">
    <div class="page-stack">
        <x-page-header :title="$editing ? 'Ubah Kategori' : 'Tambah Kategori'" />

        <form method="POST" novalidate class="form-card" style="max-width:560px"
              action="{{ $editing ? route('kategori.update', $category) : route('kategori.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <x-field label="Nama Kategori" name="name" required>
                <input type="text" id="name" name="name" maxlength="50"
                       value="{{ old('name', $category->name) }}"
                       class="input-control @error('name') input-error @enderror"
                       placeholder="Contoh: Peralatan Survival">
            </x-field>

            <div class="form-actions">
                <a href="{{ route('kategori.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary"><i data-lucide="save" width="16" height="16"></i> Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
