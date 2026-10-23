@php $editing = $member->exists; @endphp
<x-layouts.app :title="$editing ? 'Ubah Anggota' : 'Tambah Anggota'">
    <div class="page-stack">
        <x-page-header :title="$editing ? 'Ubah Anggota' : 'Tambah Anggota'" description="Role akun otomatis Anggota." />

        <form method="POST" enctype="multipart/form-data" novalidate class="form-card" style="max-width:760px"
              action="{{ $editing ? route('anggota.update', $member) : route('anggota.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <x-field label="Email" name="email" required>
                    <input type="email" id="email" name="email" maxlength="100" value="{{ old('email', $member->email) }}"
                           class="input-control @error('email') input-error @enderror" autocomplete="off">
                </x-field>

                <x-field label="Password" name="password" :required="! $editing"
                         :hint="$editing ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal '.config('gudang.password_min').' karakter.'">
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           class="input-control @error('password') input-error @enderror">
                </x-field>

                <x-profile-fields :user="$member" />
            </div>

            <div class="form-actions">
                <a href="{{ route('anggota.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary"><i data-lucide="save" width="16" height="16"></i> Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app>
