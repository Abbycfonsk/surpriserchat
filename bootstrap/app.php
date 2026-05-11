<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ⭐ ALIAS DE MIDDLEWARES PERSONALIZADOS
        $middleware->alias([
            'geniusPrivilege' => \App\Http\Middleware\CheckGeniusPrivileges::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check.suspended' => \App\Http\Middleware\CheckSuspended::class,
        ]);

        // ⭐ GRUPO API REAL (sin sesiones, sin cookies, sin Sanctum stateful)
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
   
   
    
    ->withExceptions(function (Exceptions $exceptions): void {

        // ⭐ MANEJO PERSONALIZADO DE ARCHIVOS DEMASIADO GRANDES
        $exceptions->render(function (ValidationException $e, $request) {

            if ($request->expectsJson()) {

                // Si el error viene del campo "image"
                if ($e->validator->errors()->has('image')) {
                    return response()->json([
                        'message' => 'El archivo es demasiado grande. Máximo permitido: 50 MB.',
                        'errors' => [
                            'image' => ['El archivo supera el tamaño máximo permitido.']
                        ]
                    ], 422);
                }

                // Otros errores de validación
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
    })
      ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {

        // ⭐ CRON CADA 3 HORAS
        $schedule->call(function () {
            app(\App\Http\Controllers\NotificationController::class)->sendAdsSummary();
        })->everyThreeHours();

    })
    ->create();
  
  