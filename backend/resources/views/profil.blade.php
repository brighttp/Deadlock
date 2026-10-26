<x-layouts.app title="Terminal Profil Personel">
    <div class="page-stack">
        <x-page-header title="Profil Saya" description="Satu akun memiliki tepat satu profil. Anda hanya dapat mengubah profil sendiri." />

        <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" novalidate class="form-card">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <x-field label="Email" name="email_readonly" hint="Email hanya dapat diubah oleh Admin.">
                    <input type="email" id="email_readonly" value="{{ $user->email }}" class="input-control" disabled>
                </x-field>
                <x-field label="Role" name="role_readonly">
                    <div><x-badge :status="$user->isAdmin() ? 'Admin' : 'Survivor'" /></div>
                </x-field>

                <x-profile-fields :user="$user" />

                <x-field label="Mata Uang Tampilan" name="currency" hint="Nominal denda ditampilkan dengan kurs harian Bank Sentral Eropa. Cetak dan PDF tetap Rupiah.">
                    <select id="currency" name="currency" class="input-control @error('currency') input-error @enderror">
                        @foreach (config('gudang.mata_uang.daftar') as $kode => $info)
                            <option value="{{ $kode }}" @selected(old('currency', $user->currency ?? 'IDR') === $kode)>{{ $kode }} - {{ $info['nama'] }}</option>
                        @endforeach
                    </select>
                </x-field>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i data-lucide="save" width="16" height="16"></i> Simpan Profil</button>
            </div>
        </form>

        <form method="POST" action="{{ route('profil.password') }}" novalidate class="form-card">
            @csrf
            @method('PUT')
            <h3 class="section-title" style="margin-bottom:14px">Ganti Password</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="password" class="form-label"><span>Password Baru<span class="required-dot">*</span></span></label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           class="input-control @error('password', 'password') input-error @enderror">
                    @error('password', 'password')
                        <span class="form-error">{{ $message }}</span>
                    @else
                        <span class="form-hint">Minimal {{ config('gudang.password_min') }} karakter.</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label"><span>Konfirmasi Password<span class="required-dot">*</span></span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="input-control">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-primary"><i data-lucide="key-round" width="16" height="16"></i> Ganti Password</button>
            </div>
        </form>
    </div>
</x-layouts.app>
