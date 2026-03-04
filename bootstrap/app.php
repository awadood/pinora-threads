<?php

use App\Exceptions\OutOfStockException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/api/health',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->appendToGroup('api', [\App\Http\Middleware\ResolveStoreContext::class]);

        // If CloudFront/proxy is in front of app, trust forwarded headers as needed.
        // $middleware->trustProxies('127.0.0.1', Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $t, Request $request) {
            if (! $request->expectsJson()) {
                return null; // fall back to Laravel's default exception handler
            }

            $make = function (int $status, string $message, array $errors, array $headers, Throwable $t): JsonResponse {
                return response()->json([
                    'status' => $status,
                    'message' => $message,
                    'errors' => $errors ?? null,
                    'trace' => config('app.env') !== 'production' ? $t->getTrace() : null,
                ], $status, $headers);
            };

            return match (true) {
                // common exceptions
                $t instanceof HttpExceptionInterface => $make(
                    $t->getStatusCode(), $t->getMessage() ?? 'Http Error', [], $t->getHeaders(), $t),
                $t instanceof AuthenticationException => $make(401, $t->getMessage(), [], [], $t),
                $t instanceof AuthorizationException => $make(403, $t->getMessage(), [], [], $t),
                $t instanceof NotFoundHttpException => $make(404, 'Resource not found.', [], [], $t),
                $t instanceof ValidationException => $make($t->status, $t->getMessage(), $t->errors(), [], $t),

                // database exceptions
                $t instanceof QueryException => match ((string) $t->getCode()) {
                    '7' => $make(500, 'Database is currently unavailable.', [], [], $t),
                    '22P02' => $make(500, 'Invalid text representation.', [], [], $t),
                    '23503' => $make(500, 'Foreign key violation', [], [], $t),
                    '23505' => $make(500, 'Unique constraint violation', [], [], $t),
                    '42703' => $make(500, 'Undefined column', [], [], $t),
                    default => $make(500, 'Database error'.$t->getCode(), [], [], $t),
                },

                // business exceptions
                $t instanceof OutOfStockException => $make($t->getCode(), $t->getMessage(), [], [], $t),

                default => $make(500, $t->getMessage(), [], [], $t),
            };
        });
    })->create();
