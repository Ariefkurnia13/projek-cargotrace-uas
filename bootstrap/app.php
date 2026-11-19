<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Safe, consistent JSON response on API errors without stack trace leakage
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;
                
                // Allow validation exceptions to bubble normally (status 422)
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The given data was invalid.',
                        'errors' => $e->errors(),
                    ], 422);
                }

                if ($statusCode === 404) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested resource not found.',
                    ], 404);
                }

                if ($statusCode === 403) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Forbidden: Insufficient role permissions.',
                    ], 403);
                }

                if ($statusCode === 401) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated.',
                    ], 401);
                }

                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'An internal server error occurred.',
                ], $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500);
            }
        });
    })->create();
