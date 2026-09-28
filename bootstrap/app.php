<?php

use App\Support\EntradaAntigua;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Formularios web rechazados: igual que Laravel (Handler::invalid), pero sin
        // guardar para old() arrays donde el formulario espera texto (ver EntradaAntigua).
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null; // respuesta JSON por defecto
            }

            $bolsa = $request->input('_error_bag');

            return redirect($e->redirectTo ?? url()->previous())
                ->withInput(EntradaAntigua::limpiar($request->input(), $e->validator->getRules()))
                ->withErrors($e->errors(), is_string($bolsa) ? $bolsa : $e->errorBag);
        });
    })->create();
