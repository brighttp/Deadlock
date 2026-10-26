<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionProbe extends AbstractProbe
{
    public function kunci(): string
    {
        return 'db_koneksi';
    }

    protected function label(): string
    {
        return 'Koneksi database';
    }

    protected function satuan(): string
    {
        return 'koneksi';
    }

    protected function baca(): Metric
    {
        if (DB::getDriverName() !== 'pgsql') {
            return $this->metric(1, null); // SQLite (test): satu koneksi, tanpa batas
        }

        $row = DB::selectOne("SELECT count(*) AS dipakai, current_setting('max_connections')::int AS maks FROM pg_stat_activity");

        // Batas = nilai terkecil antara konfigurasi dan max_connections server.
        return $this->metric((float) $row->dipakai, (float) min($row->maks, $this->batas() ?? $row->maks));
    }
}
