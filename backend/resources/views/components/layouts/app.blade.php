@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $nav = $isAdmin
        ? [
            ['profil.edit', 'Profil Komandan', 'user-check', 'profil*'],
            ['dashboard', 'Dashboard Komando', 'layout-dashboard', 'dashboard'],
            ['kategori.index', 'Kategori Logistik', 'tags', 'kategori*'],
            ['unit.index', 'Unit Inventaris', 'boxes', 'unit*'],
            ['katalog', 'Katalog Unit', 'package-search', 'katalog*'],
            ['anggota.index', 'Personel Survivor', 'users', 'anggota*'],
            ['pinjaman.index', 'Peminjaman Unit', 'arrow-left-right', 'pinjaman*'],
            ['riwayat.index', 'Riwayat & Rekap', 'history', 'riwayat*'],
        ]
        : [
            ['profil.edit', 'Profil Saya', 'user-check', 'profil*'],
            ['dashboard', 'Dashboard', 'layout-dashboard', 'dashboard'],
            ['katalog', 'Katalog Unit', 'package-search', 'katalog*'],
            ['pinjaman-saya', 'Pinjaman Saya', 'clipboard-list', 'pinjaman-saya*'],
        ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Gudang' }} · Deadlock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/deadlock.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="app-root">
    <aside class="app-sidebar" id="sidebar">
        <a href="{{ route('profil.edit') }}" class="sidebar-brand sidebar-profile-header" style="text-decoration:none" title="Buka Profil">
            <div class="user-avatar-wrapper">
                <div class="user-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 2)) }}</div>
                <span class="user-online-pip"></span>
            </div>
            <div class="user-details">
                <span class="user-name" title="{{ $user->name }}">{{ $user->name }}</span>
                <span class="user-role-text">{{ $isAdmin ? 'KOMANDAN LOGISTIK' : 'SURVIVOR RECON' }}</span>
            </div>
        </a>

        <nav class="sidebar-nav">
            <div class="nav-items-list">
                @foreach ($nav as [$route, $label, $icon, $pattern])
                    @php $active = request()->is($pattern); @endphp
                    <a href="{{ route($route) }}" class="nav-item {{ $active ? 'active' : '' }}" style="text-decoration:none">
                        <span class="nav-item-icon"><i data-lucide="{{ $icon }}" width="18" height="18"></i></span>
                        <span class="nav-item-label">{{ $label }}</span>
                        @if ($active)
                            <span class="nav-item-active-chevron"><i data-lucide="chevron-right" width="14" height="14"></i></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="comms-status-bar">
                <i data-lucide="radio" width="12" height="12" class="comms-radio-icon"></i>
                <span class="comms-label">RADIO FREQ: 142.85 MHz</span>
                <span class="comms-indicator"></span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-sidebar-logout">
                    <i data-lucide="log-out" width="15" height="15"></i>
                    <span>Keluar Sesi Terminal</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-topbar">
            <div class="topbar-left">
                <button type="button" class="btn-icon mobile-menu-btn" title="Buka Menu"
                        onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i data-lucide="menu" width="20" height="20"></i>
                </button>
                <div class="topbar-page-identity">
                    <div class="topbar-page-title-row">
                        <span class="topbar-page-title">{{ $title ?? 'Sistem Gudang' }}</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-clock-widget">
                    <i data-lucide="clock" width="13" height="13" class="clock-icon"></i>
                    <span class="clock-text" id="clock">{{ now()->format('H:i:s') }} WIB</span>
                </div>
                <x-badge :status="$isAdmin ? 'Admin' : 'Survivor'" class="role-badge-pill" />
            </div>
        </header>

        <main class="app-content">
            {{ $slot }}
        </main>
    </div>
</div>

<x-flash />
@stack('modals')

<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
    lucide.createIcons();

    // Jam topbar (hanya tampilan).
    setInterval(() => {
        const el = document.getElementById('clock');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
    }, 1000);

    // Modal sederhana: [data-modal-open="id"] membuka, [data-modal-close] / klik overlay / Escape menutup.
    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-modal-open]');
        if (opener) document.getElementById(opener.dataset.modalOpen).hidden = false;
        if (e.target.closest('[data-modal-close]') || e.target.classList.contains('modal-overlay')) {
            e.target.closest('.modal-overlay').hidden = true;
        }
        if (e.target.closest('[data-dismiss-toast]')) e.target.closest('.toast-item').remove();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach((m) => m.hidden = true);
    });
</script>
@stack('scripts')
</body>
</html>
