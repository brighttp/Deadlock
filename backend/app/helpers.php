<?php

use App\Services\KursService;
use Carbon\CarbonInterface;

if (! function_exists('rupiah')) {
    function rupiah(int $amount): string
    {
        return 'Rp'.number_format($amount, 0, ',', '.');
    }
}

if (! function_exists('tanggal')) {
    function tanggal(?CarbonInterface $date): string
    {
        return $date ? $date->translatedFormat('d M Y') : '-';
    }
}

if (! function_exists('uang')) {
    /** Nominal Rupiah dalam mata uang pilihan user yang login (default Rupiah). Cetak/PDF tetap memakai rupiah(). */
    function uang(int $rupiah): string
    {
        $mataUang = auth()->user()?->currency ?? config('gudang.mata_uang.default');

        return app(KursService::class)->format($rupiah, $mataUang);
    }
}
