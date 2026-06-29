<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    //  ->withBroadcasting(                                    // ← ajouter ce bloc
    //     __DIR__.'/../routes/channels.php',
    //     ['prefix' => 'api', 'middleware' => ['auth:api']],
    // )
    ->withMiddleware(function (Middleware $middleware): void {
 $middleware->validateCsrfTokens(except: [
        'telescope/*',
    ]);
        $middleware->prependToGroup('api', \Illuminate\Http\Middleware\HandleCors::class);

        $middleware->alias([
        'is_municipal_admin' => \App\Http\Middleware\isMunicipalAdminMiddleware::class,
        'is_admin' => \App\Http\Middleware\IsAdminMiddleware::class,
        'is_citizen' => \App\Http\Middleware\IsCitizenMiddleware::class,
        'is_agent' => \App\Http\Middleware\isAgentMiddleware::class,
        ]);
    })
   ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->shouldRenderJsonWhen(function ($request, $e) {
        return $request->is('api/*') || $request->wantsJson();
    });
})->create();
