<?php

namespace App\Http\Middleware;

use App\Monitoring\ModeTerbatas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Selama mode terbatas aktif, request yang berhasil menandakan database mungkin sudah pulih:
 * cek koneksi sekali lalu kembalikan aplikasi ke kondisi normal (tanpa menunggu scheduler).
 */
class PulihkanModeTerbatas
{
    public function __construct(private ModeTerbatas $mode) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 503 && $this->mode->aktif()) {
            $this->mode->cobaPulihkan();
        }

        return $response;
    }
}
