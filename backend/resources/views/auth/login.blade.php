<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Deadlock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/deadlock.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-viewport">
    <div class="auth-scanlines"></div>

    <header class="auth-header-hud">
        <div class="auth-hud-left">
            <span class="auth-hud-pip"></span>
            <span>// DEADLOCK TERMINAL v2.4.0</span>
        </div>
        <div class="auth-hud-center">
            <i data-lucide="shield" width="13" height="13" style="color: var(--color-accent)"></i>
            <span>HAVEN SECTOR 04 &bull; ARSENAL &amp; QUARANTINE PROTOCOL</span>
        </div>
        <div class="auth-hud-right">
            <span class="auth-hud-defcon">DEFCON-2 LOCKDOWN</span>
        </div>
    </header>

    <main class="auth-main-layout">
        <section class="auth-holo-stage">
            <div class="auth-holo-header">
                <div class="auth-holo-tag">
                    <i data-lucide="cpu" width="15" height="15" style="color:#4ADE80"></i>
                    <span>TACTICAL VAULT CORE // BIOMETRIC FEED</span>
                </div>
            </div>
            <div class="holo-telemetry-strip">
                <div class="holo-pill"><span class="pill-title">Mainframe</span><span class="pill-val">ONLINE 99.8%</span></div>
                <div class="holo-pill"><span class="pill-title">Enkripsi</span><span class="pill-val amber">AES-256 GCM</span></div>
                <div class="holo-pill"><span class="pill-title">Karantina</span><span class="pill-val">HARI KE-142</span></div>
            </div>
            <div class="holo-briefing-box">
                <p style="margin:0; font-weight:500">&ldquo;Dalam kegelapan kiamat, disiplin inventaris logistik adalah satu-satunya garis batas antara bertahan hidup dan kepunahan massal.&rdquo;</p>
            </div>
        </section>

        <section class="auth-terminal-card">
            <span class="corner-bracket corner-tl"></span>
            <span class="corner-bracket corner-tr"></span>
            <span class="corner-bracket corner-bl"></span>
            <span class="corner-bracket corner-br"></span>

            <div class="auth-brand-hero">
                <div class="auth-brand-logo-frame"><i data-lucide="shield" width="28" height="28"></i></div>
                <div>
                    <h1 class="auth-brand-title">DEADLOCK</h1>
                    <p class="auth-brand-subtitle">Sistem Logistik Gudang Karantina // Haven Sector 4</p>
                </div>
            </div>

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

            <p class="form-hint" style="margin-top:14px; text-align:center">Tidak ada registrasi. Akun dibuat oleh Admin.</p>
        </section>
    </main>

    <footer class="auth-footer-hud">
        <span>ENCRYPTED LOGISTICS MAINFRAME // HAVEN REFUGEE PROTECTION PROTOCOL</span>
    </footer>
</div>
<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
