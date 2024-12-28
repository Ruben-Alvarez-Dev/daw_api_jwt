<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Suppress deprecation warnings in the response
                error_reporting(E_ALL & ~E_DEPRECATED);
                
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], $e instanceof HttpException ? $e->getStatusCode() : 500);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
            
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'status_code' => $status
            ], $status);
        }

        return parent::render($request, $e);
    }
}
