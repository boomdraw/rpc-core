<?php

namespace Boomdraw\RpcCore\Exceptions;

use Boomdraw\RpcCore\Request as RpcRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        InvalidParamsRpcException::class,
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof RpcException) {
            return $exception->toResponse($request);
        }
        if (! $request instanceof RpcRequest) {
            return parent::render($request, $exception);
        }
        if (method_exists($exception, 'getStatusCode')) {
            $code = $exception->getStatusCode();
        } else {
            $code = $exception->getCode();
        }
        $message = $exception->getMessage();
        if ($exception instanceof ModelNotFoundException) {
            $exception = RpcException::make($message, -404);
        } elseif ($exception instanceof AuthorizationException) {
            $exception = RpcException::make($message, -403);
        } elseif ($exception instanceof ValidationException) {
            $exception = InvalidParamsRpcException::make($exception->errors());
        } elseif (500 > $code && $code >= 400) {
            $exception = RpcException::make($message, -$code);
        } else {
            $exception = ServerErrorRpcException::make($message);
        }

        return parent::render($request, $exception);
    }
}
