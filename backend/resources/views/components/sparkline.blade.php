@props(['nilai' => [], 'batas' => null, 'warna' => '#4ADE80'])
@php
    // Grafik garis riwayat + garis putus-putus batas atas. Sampel null (gagal diukur) memutus garis.
    $w = 260; $h = 56; $pad = 4;
    $angka = array_filter($nilai, fn ($v) => $v !== null);
    $maks = max([...$angka, $batas ?? 0, 1]) * 1.1;
    $n = count($nilai);
    $x = fn ($i) => $n > 1 ? $pad + $i * ($w - 2 * $pad) / ($n - 1) : $w / 2;
    $y = fn ($v) => $h - $pad - ($v / $maks) * ($h - 2 * $pad);

    $segmen = [[]];
    foreach ($nilai as $i => $v) {
        if ($v === null) { $segmen[] = []; continue; }
        $segmen[count($segmen) - 1][] = round($x($i), 1).','.round($y($v), 1);
    }
    $gagal = array_keys(array_filter($nilai, fn ($v) => $v === null));
@endphp
@if (count($angka) < 2)
    <div class="sparkline-empty">Riwayat belum cukup. Jalankan pemeriksaan beberapa kali.</div>
@else
    <svg viewBox="0 0 {{ $w }} {{ $h }}" class="sparkline" preserveAspectRatio="none" role="img"
         aria-label="Riwayat {{ count($nilai) }} sampel{{ $batas ? ', batas atas '.$batas : '' }}">
        @if ($batas)
            <line x1="0" x2="{{ $w }}" y1="{{ round($y($batas), 1) }}" y2="{{ round($y($batas), 1) }}"
                  stroke="var(--color-danger)" stroke-width="1" stroke-dasharray="4 3" vector-effect="non-scaling-stroke" />
        @endif
        @foreach ($segmen as $titik)
            @if (count($titik) > 1)
                <polyline points="{{ implode(' ', $titik) }}" fill="none" stroke="{{ $warna }}" stroke-width="2"
                          stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
            @elseif (count($titik) === 1)
                @php [$cx, $cy] = explode(',', $titik[0]); @endphp
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="2" fill="{{ $warna }}" />
            @endif
        @endforeach
        @foreach ($gagal as $i)
            <line x1="{{ round($x($i), 1) }}" x2="{{ round($x($i), 1) }}" y1="{{ $pad }}" y2="{{ $h - $pad }}"
                  stroke="var(--color-danger)" stroke-width="1" opacity=".5" vector-effect="non-scaling-stroke" />
        @endforeach
    </svg>
@endif
