<?php

namespace App\Monitoring\Probes;

use App\Monitoring\Metric;

interface ResourceProbe
{
    public function ukur(): Metric;
}
