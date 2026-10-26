<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\PulihkanModeTerbatas;
use App\Monitoring\ModeTerbatas;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        $middleware->web(append: [
            PulihkanModeTerbatas::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Graceful failure: database tidak bisa dihubungi => halaman mode terbatas (503), bukan error 500.
        $exceptions->render(function (Throwable $e, Request $request) {
            $mode = app(ModeTerbatas::class);
            if (! $mode->koneksiGagal($e)) {
                return null;
            }

            $mode->masuk($e);

            return response()->view('errors.mode-terbatas', [
                'snapshot' => $mode->snapshotKatalog(),
                'sejak' => $mode->sejak(),
            ], 503);
        });
    })->create();
