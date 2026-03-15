<?php

use App\Http\Middleware\EnsureApiAcceptsJson;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureApiAcceptsJson::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => __('Unauthenticated.')], 401);
            }
        });
        $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $message = $e->getModel() === \App\Models\User::class
                    ? __('User not found.')
                    : __('Record not found.');

                return response()->json(['message' => $message], 404);
            }
        });
        // Laravel converts ModelNotFoundException to NotFoundHttpException before render;
        // handle it so API gets clean JSON instead of the framework message + trace.
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if (($request->is('api/*') || $request->expectsJson()) && $e->getStatusCode() === 404) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'App\Models\User') || str_contains($msg, 'App\\\\Models\\\\User')) {
                    return response()->json(['message' => __('User not found.')], 404);
                }
                if (str_contains($msg, 'No query results for model')) {
                    return response()->json(['message' => __('Record not found.')], 404);
                }
            }
        });
    })->create();
