<?php

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
