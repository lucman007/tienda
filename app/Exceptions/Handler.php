<?php

namespace sysfact\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return response()->view('errors.403_custom', ['usuario'=>auth()->user()->persona], 403);
        }
        if ($exception instanceof InvalidSignatureException) {
            return response()
                ->view('errors.enlace-expirado', [
                    'mensaje' => 'El enlace ha expirado. Por favor, solicite nuevamente los documentos.'
                ], 410);
        }
        return parent::render($request, $exception);
    }
}
