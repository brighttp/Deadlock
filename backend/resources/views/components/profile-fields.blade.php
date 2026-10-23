{{-- Field profil (nama lengkap, HP, alamat, foto) yang dipakai form Anggota dan form Profil. --}}
@props(['user'])
@php $photo = $user->profile?->photo; @endphp
<x-field label="Nama Lengkap" name="name" required>
    <input type="text" id="name" name="name" maxlength="100" value="{{ old('name', $user->name) }}"
           class="input-control @error('name') input-error @enderror">
</x-field>

<x-field label="Nomor HP" name="phone" hint="Diawali 08, 10–13 digit. Contoh: 081233445566">
    <input type="text" id="phone" name="phone" inputmode="numeric" maxlength="20"
           value="{{ old('phone', $user->profile?->phone) }}"
           class="input-control text-mono @error('phone') input-error @enderror">
</x-field>

<x-field label="Alamat / Basecamp" name="address" class="span-2">
    <textarea id="address" name="address" class="input-control @error('address') input-error @enderror">{{ old('address', $user->profile?->address) }}</textarea>
</x-field>

<x-field label="Foto" name="photo" class="span-2" hint="Opsional. jpg, jpeg, png, atau webp, maksimal 2 MB.">
    <div style="display:flex; gap:14px; align-items:center">
        <img id="photo-preview" class="photo-preview" alt="Pratinjau foto"
             src="{{ $photo ? asset('storage/'.$photo) : '' }}" @if (! $photo) hidden @endif>
        <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp"
               class="input-control @error('photo') input-error @enderror"
               onchange="const f=this.files[0], p=document.getElementById('photo-preview'); if (f) { p.src = URL.createObjectURL(f); p.hidden = false; }">
    </div>
</x-field>
