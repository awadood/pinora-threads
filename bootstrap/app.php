<?php

use App\Exceptions\OutOfStockException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $t, Request $request) {
            if (! $request->expectsJson()) {
                return null; // fall back to Laravel's default exception handler
            }

            $respond = function (int $code, string $message, Throwable $throwable): JsonResponse {
                return response()->json([
                    'error' => [
                        'code' => $code,
                        'message' => $message,
                        'trace' => $throwable->getTrace(),
                    ]
                ]);
            };

            return match (true) {
                $t instanceof QueryException => match ((string) $t->getCode()) {
                    '23505' => $respond(500, 'Database is currently unavailable.', $t),
                    '7' => $respond(500, 'Unique constraint violation', $t),
                    default => $respond(500, 'Database error', $t),
                },

                $t instanceof NotFoundHttpException => $respond(404, 'Resource not found.', $t),

                $t instanceof OutOfStockException  => $respond($t->getCode(), $t->getMessage(), $t),

                default => $respond(500, $t->getMessage(), $t),
            };
        });
    })->create();
