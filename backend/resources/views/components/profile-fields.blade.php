{{-- Field profil (nama lengkap, HP, alamat) yang dipakai form Anggota dan form Profil. --}}
@props(['user'])
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
