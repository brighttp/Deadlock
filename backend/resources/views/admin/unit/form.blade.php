@php
    $editing = $unit->exists;
    $selected = collect(old('categories', $unit->categories->pluck('id')->all()))->map(fn ($id) => (int) $id);
@endphp
<x-layouts.app :title="$editing ? 'Ubah Unit' : 'Tambah Unit'">
    <div class="page-stack">
        <x-page-header :title="$editing ? 'Ubah Unit' : 'Tambah Unit'" />

        <form method="POST" novalidate class="form-card" style="max-width:760px"
              action="{{ $editing ? route('unit.update', $unit) : route('unit.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <x-field label="Kode Unit" name="unit_code" required>
                    <input type="text" id="unit_code" name="unit_code" maxlength="20"
                           value="{{ old('unit_code', $unit->unit_code) }}"
                           class="input-control text-mono @error('unit_code') input-error @enderror" placeholder="UNIT-013">
                </x-field>

                <x-field label="Nama Unit" name="name" required hint="Nama boleh sama dengan unit lain.">
                    <input type="text" id="name" name="name" maxlength="100"
                           value="{{ old('name', $unit->name) }}"
                           class="input-control @error('name') input-error @enderror">
                </x-field>

                <x-field label="Deskripsi" name="description" class="span-2">
                    <textarea id="description" name="description"
                              class="input-control @error('description') input-error @enderror">{{ old('description', $unit->description) }}</textarea>
                </x-field>

                <x-field label="Kategori" name="categories" required class="span-2" hint="Boleh pilih lebih dari satu.">
                    <div class="tag-options">
                        @foreach ($categories as $category)
                            <label class="tag-option {{ $selected->contains($category->id) ? 'selected' : '' }}">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                       @checked($selected->contains($category->id))
                                       onchange="this.parentElement.classList.toggle('selected', this.checked)">
                                {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                </x-field>

                <x-field label="Status" name="status" class="span-2" hint="Diatur otomatis oleh sistem saat pinjam/kembali.">
                    <div><x-badge :status="ucfirst($unit->status)" /></div>
                </x-field>
            </div>

            <div class="form-actions">
                <a href="{{ route('unit.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary"><i data-lucide="save" width="16" height="16"></i> Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
