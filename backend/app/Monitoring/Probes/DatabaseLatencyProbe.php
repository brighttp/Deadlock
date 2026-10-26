<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;
use Illuminate\Support\Facades\DB;

class DatabaseLatencyProbe extends AbstractProbe
{
    public function kunci(): string
    {
        return 'db_latensi_ms';
    }

    protected function label(): string
    {
        return 'Latensi database';
    }

    protected function satuan(): string
    {
        return 'ms';
    }

    protected function baca(): Metric
    {
        DB::connection()->getPdo(); // buka koneksi dulu: yang diukur waktu query, bukan handshake TLS
        $mulai = hrtime(true);
        DB::select('SELECT 1');

        return $this->metric(round((hrtime(true) - $mulai) / 1e6, 1));
    }
}
