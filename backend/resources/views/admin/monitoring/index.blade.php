<x-layouts.app title="Monitoring Sistem">
    @php
        $warna = [
            'normal' => '#4ADE80',
            'peringatan' => 'var(--color-warning)',
            'kritis' => 'var(--color-danger)',
            'gagal' => 'var(--color-danger)',
        ];
        $badge = ['normal' => 'badge-success', 'peringatan' => 'badge-warning', 'kritis' => 'badge-danger', 'gagal' => 'badge-danger'];
        $waktu = fn ($iso) => $iso ? \Carbon\Carbon::parse($iso)->timezone(config('app.timezone'))->translatedFormat('d M Y H:i') : '-';
    @endphp

    <div class="page-stack">
        <x-page-header title="Monitoring Sistem"
                       description="Pemakaian resource kritikal terhadap batas atasnya. Peringatan mulai {{ config('gudang.monitoring.peringatan_persen') }}%, kritis mulai {{ config('gudang.monitoring.kritis_persen') }}%.">
            <x-slot:actions>
                <form method="POST" action="{{ route('monitoring.pantau') }}">
                    @csrf
                    <button type="submit" class="btn-primary"><i data-lucide="refresh-cw" width="15" height="15"></i> Jalankan Pemeriksaan</button>
                </form>
            </x-slot:actions>
        </x-page-header>

        @if ($modeTerbatas)
            <div class="alert-box danger"><i data-lucide="alert-octagon" width="16" height="16"></i>
                <span>Aplikasi sedang dalam <strong>mode terbatas</strong> karena database sempat tidak dapat dihubungi.</span></div>
        @endif

        <div class="monitor-grid">
            @foreach ($metrics as $kunci => $m)
                @php $status = $m->status(); @endphp
                <div class="stat-card monitor-card">
                    <div class="stat-header">
                        <span class="stat-label">{{ $m->label }}</span>
                        <span class="badge {{ $badge[$status] }}"><span class="badge-dot"></span><span>{{ ucfirst($status) }}</span></span>
                    </div>
                    <div class="stat-value monitor-value" style="color: {{ $warna[$status] }}">{{ $m->teks() }}</div>

                    @if ($m->persen() !== null)
                        <div class="monitor-meter" title="{{ $m->persen() }}% dari batas atas">
                            <div class="monitor-meter-fill" style="width: {{ min(100, $m->persen()) }}%; background: {{ $warna[$status] }}"></div>
                            <span class="monitor-tick" style="left: {{ config('gudang.monitoring.peringatan_persen') }}%"></span>
                            <span class="monitor-tick tick-kritis" style="left: {{ config('gudang.monitoring.kritis_persen') }}%"></span>
                        </div>
                        <div class="stat-sub">{{ $m->persen() }}% dari batas atas</div>
                    @elseif ($status === 'gagal')
                        <div class="stat-sub text-danger" title="{{ $m->pesanGagal }}">{{ \Illuminate\Support\Str::limit($m->pesanGagal, 90) }}</div>
                    @else
                        <div class="stat-sub">Tidak memiliki batas atas</div>
                    @endif

                    <x-sparkline :nilai="$seri[$kunci]" :batas="$m->batas" :warna="$warna[$status]" />
                </div>
            @endforeach
        </div>
        <p class="stat-sub">
            Grafik: {{ $jumlahSampel }} sampel dari {{ $waktu($sampelPertama) }} sampai {{ $waktu($sampelTerakhir) }}.
            Garis merah putus-putus = batas atas. Sampel diambil oleh <span class="text-mono">gudang:pantau</span> setiap 5 menit.
        </p>

        <div class="monitor-columns">
            <div>
                <h3 class="section-title" style="margin-bottom:10px">Alert Aktif</h3>
                <div class="table-container">
                    @if (empty($alertAktif))
                        <x-empty title="Tidak ada alert aktif." description="Semua masalah yang pernah terdeteksi sudah tertangani." />
                    @else
                        <table class="tactical-table">
                            <thead><tr><th>Masalah</th><th>Sejak</th><th style="text-align:right">Dikirim</th></tr></thead>
                            <tbody>
                            @foreach ($alertAktif as $a)
                                <tr>
                                    <td><strong>{{ $a['judul'] }}</strong><div class="stat-sub">{{ $a['detail'] }}</div></td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($a['sejak'])->timezone(config('app.timezone'))->translatedFormat('d M H:i') }}</td>
                                    <td style="text-align:right">{{ $a['jumlah_kirim'] }}×</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            <div>
                <h3 class="section-title" style="margin-bottom:10px">Log Alert Terakhir</h3>
                <div class="table-container monitor-log">
                    @forelse ($logAlert as $baris)
                        <div class="text-mono">{{ $baris }}</div>
                    @empty
                        <x-empty title="Belum ada catatan alert hari ini." />
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
