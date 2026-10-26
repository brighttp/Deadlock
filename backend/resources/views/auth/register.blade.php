<x-layouts.auth title="Daftar">
    <form method="POST" action="{{ route('register') }}" novalidate style="display:flex; flex-direction:column; gap:14px">
        @csrf
        <x-field label="Nama Lengkap" name="name" required>
            <div class="tactical-input-wrap">
                <i data-lucide="user" width="16" height="16" class="tactical-input-icon"></i>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="input-control @error('name') input-error @enderror"
                       placeholder="Rick Grimes" autocomplete="name" autofocus>
            </div>
        </x-field>

        <x-field label="Alamat Email" name="email" required>
            <div class="tactical-input-wrap">
                <i data-lucide="mail" width="16" height="16" class="tactical-input-icon"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="input-control @error('email') input-error @enderror"
                       placeholder="nama@haven.org" autocomplete="email">
            </div>
        </x-field>

        <x-field label="Nomor HP" name="phone">
            <div class="tactical-input-wrap">
                <i data-lucide="phone" width="16" height="16" class="tactical-input-icon"></i>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                       class="input-control @error('phone') input-error @enderror"
                       placeholder="081234567890" autocomplete="tel">
            </div>
        </x-field>

        <x-field label="Alamat" name="address">
            <div class="tactical-input-wrap">
                <i data-lucide="map-pin" width="16" height="16" class="tactical-input-icon"></i>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       class="input-control @error('address') input-error @enderror"
                       placeholder="Blok C, Haven Sector 04" autocomplete="street-address">
            </div>
        </x-field>

        <x-field label="Password" name="password" required>
            <div class="tactical-input-wrap">
                <i data-lucide="lock" width="16" height="16" class="tactical-input-icon"></i>
                <input type="password" id="password" name="password"
                       class="input-control @error('password') input-error @enderror"
                       placeholder="Minimal {{ config('gudang.password_min') }} karakter" autocomplete="new-password">
            </div>
        </x-field>

        <x-field label="Konfirmasi Password" name="password_confirmation" required>
            <div class="tactical-input-wrap">
                <i data-lucide="lock" width="16" height="16" class="tactical-input-icon"></i>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="input-control" placeholder="Ulangi password" autocomplete="new-password">
            </div>
        </x-field>

        <button type="submit" class="btn-primary" style="width:100%; margin-top:6px">
            Daftar sebagai Survivor <i data-lucide="user-plus" width="17" height="17"></i>
        </button>
    </form>

    <p class="form-hint" style="margin-top:14px; text-align:center">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
    </p>
</x-layouts.auth>
