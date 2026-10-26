<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;
use Illuminate\Support\Facades\Storage;

class PhotoStorageProbe extends AbstractProbe
{
    public function kunci(): string
    {
        return 'foto_mb';
    }

    protected function label(): string
    {
        return 'Penyimpanan foto profil';
    }

    protected function satuan(): string
    {
        return 'MB';
    }

    protected function baca(): Metric
    {
        $disk = Storage::disk('public');
        $bytes = collect($disk->allFiles('profiles'))->sum(fn ($file) => $disk->size($file));

        return $this->metric(round($bytes / 1048576, 2));
    }
}
