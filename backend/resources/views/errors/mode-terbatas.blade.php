{{-- Halaman graceful failure saat database tidak dapat dihubungi. Tidak boleh memakai auth()/session/database. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="30">
    <title>Mode Terbatas · Deadlock</title>
    <link rel="stylesheet" href="{{ asset('css/deadlock.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<main class="app-content mode-terbatas">
    <div class="page-stack">
        <div class="alert-box danger">
            <div>
                <strong>Mode Terbatas: database sedang tidak dapat dihubungi.</strong>
                <div>Peminjaman, pengembalian, dan perubahan data dinonaktifkan sementara. Admin sudah menerima alert.
                    Halaman ini mencoba tersambung lagi otomatis setiap 30 detik.</div>
                @if ($sejak)
                    <div class="stat-sub">Gangguan sejak {{ \Carbon\Carbon::createFromTimestamp($sejak)->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') }} WIB.</div>
                @endif
            </div>
        </div>

        <div class="page-header">
            <div class="page-header-info">
                <h1 class="page-title">Katalog Unit (salinan terakhir)</h1>
                <p class="page-header-desc">
                    @if ($snapshot)
                        Data per {{ \Carbon\Carbon::parse($snapshot['waktu'])->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') }} WIB. Status bisa sudah berubah.
                    @else
                        Belum ada salinan katalog yang tersimpan.
                    @endif
                </p>
            </div>
            <div class="page-header-actions"><a href="{{ url()->current() }}" class="btn-primary">Coba Lagi</a></div>
        </div>

        @if ($snapshot && count($snapshot['units']))
            <div class="table-container">
                <table class="tactical-table">
                    <thead><tr><th>Kode</th><th>Nama Unit</th><th>Kategori</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($snapshot['units'] as $unit)
                        <tr>
                            <td class="text-mono">{{ $unit['kode'] }}</td>
                            <td>{{ $unit['nama'] }}</td>
                            <td>{{ $unit['kategori'] ?: '-' }}</td>
                            <td><span class="badge {{ $unit['status'] === 'tersedia' ? 'badge-success' : 'badge-warning' }}"><span class="badge-dot"></span><span>{{ ucfirst($unit['status']) }}</span></span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</main>
</body>
</html>
