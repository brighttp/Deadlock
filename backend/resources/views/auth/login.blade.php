<x-layouts.auth title="Masuk">
    @if (session('success'))
        <p class="form-hint" role="status" style="color:#4ADE80; text-align:center; margin:0 0 12px">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ url('/login') }}" novalidate style="display:flex; flex-direction:column; gap:14px">
        @csrf
        <x-field label="Alamat Email" name="email" required>
            <div class="tactical-input-wrap">
                <i data-lucide="mail" width="16" height="16" class="tactical-input-icon"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="input-control @error('email') input-error @enderror"
                       placeholder="admin@deadlock.haven" autocomplete="email" autofocus>
            </div>
        </x-field>

        <x-field label="Password" name="password" required>
            <div class="tactical-input-wrap">
                <i data-lucide="lock" width="16" height="16" class="tactical-input-icon"></i>
                <input type="password" id="password" name="password"
                       class="input-control @error('password') input-error @enderror"
                       placeholder="••••••••" autocomplete="current-password">
            </div>
        </x-field>

        <label style="display:flex; gap:8px; align-items:center; font-size:12.5px; color:var(--text-secondary)">
            <input type="checkbox" name="remember" value="1"> Ingat saya
        </label>

        <button type="submit" class="btn-primary" style="width:100%; margin-top:6px">
            Masuk Terminal <i data-lucide="arrow-right" width="17" height="17"></i>
        </button>
    </form>

    <p class="form-hint" style="margin-top:14px; text-align:center">
        Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai Survivor</a>
    </p>
</x-layouts.auth>
