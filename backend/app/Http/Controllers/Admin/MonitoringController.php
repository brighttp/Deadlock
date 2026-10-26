<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Monitoring\AlertService;
use App\Monitoring\MetricHistory;
use App\Monitoring\ModeTerbatas;
use App\Monitoring\ResourceMonitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(ResourceMonitor $monitor, MetricHistory $riwayat, AlertService $alert, ModeTerbatas $mode): View
    {
        $metrics = $monitor->ukurSemua();
        $sampel = $riwayat->semua();

        return view('admin.monitoring.index', [
            'metrics' => $metrics,
            'seri' => collect($metrics)->mapWithKeys(fn ($m, $kunci) => [$kunci => $riwayat->seri($kunci)]),
            'sampelPertama' => $sampel[0]['waktu'] ?? null,
            'sampelTerakhir' => end($sampel)['waktu'] ?? null,
            'jumlahSampel' => count($sampel),
            'alertAktif' => $alert->aktif(),
            'logAlert' => $this->logAlertTerakhir(15),
            'modeTerbatas' => $mode->aktif(),
        ]);
    }

    public function pantau(): RedirectResponse
    {
        $kode = Artisan::call('gudang:pantau');

        return back()->with($kode === 0 ? 'success' : 'error', $kode === 0
            ? 'Pemeriksaan selesai. Semua resource dalam batas aman.'
            : 'Pemeriksaan selesai. Ada resource kritis/gagal, alert sudah dicatat.');
    }

    private function logAlertTerakhir(int $jumlah): array
    {
        $file = collect(glob(storage_path('logs/alert-*.log')) ?: [])->sort()->last();

        return $file ? array_reverse(array_slice(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -$jumlah)) : [];
    }
}
