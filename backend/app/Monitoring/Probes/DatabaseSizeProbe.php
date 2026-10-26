<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;
use Illuminate\Support\Facades\DB;

class DatabaseSizeProbe extends AbstractProbe
{
    public function kunci(): string
    {
        return 'db_ukuran_mb';
    }

    protected function label(): string
    {
        return 'Ukuran database';
    }

    protected function satuan(): string
    {
        return 'MB';
    }

    protected function baca(): Metric
    {
        $bytes = DB::getDriverName() === 'pgsql'
            ? DB::scalar('SELECT pg_database_size(current_database())')
            : DB::scalar('PRAGMA page_count') * DB::scalar('PRAGMA page_size');

        return $this->metric(round($bytes / 1048576, 2));
    }
}
