<?php

namespace App\Monitoring;

use App\Notifications\AlertAplikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Mengirim alert saat aplikasi bermasalah dan kabar pulih saat masalah selesai.
 * State alert disimpan di cache (file), bukan database, supaya tetap bekerja ketika database mati.
 */
class AlertService
{
    private const DAFTAR = 'alert:daftar';

    /**
     * Laporkan masalah. Alert pertama langsung dikirim; selama masalah belum tertangani,
     * alert yang sama baru dikirim ulang setelah gudang.alert.ulang_menit.
     *
     * @return bool true jika notifikasi benar-benar dikirim pada panggilan ini
     */
    public function laporkan(string $kunci, string $judul, string $detail): bool
    {
        $state = Cache::get("alert:{$kunci}");
        $jedaMenit = config('gudang.alert.ulang_menit');

        if ($state && now()->timestamp - $state['terakhir_kirim'] < $jedaMenit * 60) {
            return false;
        }

        $state = [
            'judul' => $judul,
            'detail' => $detail,
            'sejak' => $state['sejak'] ?? now()->timestamp,
            'terakhir_kirim' => now()->timestamp,
            'jumlah_kirim' => ($state['jumlah_kirim'] ?? 0) + 1,
        ];
        Cache::forever("alert:{$kunci}", $state);
        Cache::forever(self::DAFTAR, array_values(array_unique([...Cache::get(self::DAFTAR, []), $kunci])));

        $this->kirim($judul, $detail, false, ['kunci' => $kunci, 'ke' => $state['jumlah_kirim']]);

        return true;
    }

    /** Tandai masalah selesai dan kirim kabar pulih (hanya jika sebelumnya ada alert aktif). */
    public function pulihkan(string $kunci): bool
    {
        $state = Cache::pull("alert:{$kunci}");
        if (! $state) {
            return false;
        }

        Cache::forever(self::DAFTAR, array_values(array_diff(Cache::get(self::DAFTAR, []), [$kunci])));

        $durasi = now()->diffForHumans(Carbon::createFromTimestamp($state['sejak']), true);
        $this->kirim($state['judul'], "Masalah sudah tertangani setelah {$durasi}.", true, ['kunci' => $kunci]);

        return true;
    }

    /** @return array<string, array> alert yang masih aktif */
    public function aktif(): array
    {
        $hasil = [];
        foreach (Cache::get(self::DAFTAR, []) as $kunci) {
            if ($state = Cache::get("alert:{$kunci}")) {
                $hasil[$kunci] = $state;
            }
        }

        return $hasil;
    }

    private function kirim(string $judul, string $detail, bool $pulih, array $konteks): void
    {
        Log::channel('alert')->log($pulih ? 'info' : 'critical', ($pulih ? '[PULIH] ' : '[ALERT] ')."{$judul}: {$detail}", $konteks);

        if (! $email = config('gudang.alert.email')) {
            return;
        }

        try {
            Notification::route('mail', $email)->notifyNow(new AlertAplikasi($judul, $detail, $pulih));
        } catch (Throwable $e) {
            // Gagal kirim email tidak boleh menggagalkan aplikasi; cukup dicatat.
            Log::channel('alert')->error('Email alert gagal dikirim: '.$e->getMessage());
        }
    }
}
