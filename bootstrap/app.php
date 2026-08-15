<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\IdempotencyMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <--- ADICIONE ESTA LINHA AQUI!
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(append: [
        IdempotencyMiddleware::class,
    ]);
    $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'feature' => \App\Http\Middleware\CheckTenantFeatureFlag::class,
            'idempotency' => \App\Http\Middleware\IdempotencyMiddleware::class,
        ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
