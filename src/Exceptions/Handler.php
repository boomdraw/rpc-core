<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

use Boomdraw\RpcCore\Request as RpcRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
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
     * @param Throwable $e
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof RpcException) {
            return $e->toResponse($request);
        }
        if (! $request instanceof RpcRequest) {
            return parent::render($request, $e);
        }
        $e = $this->transformThrowableToRPCException($e);

        return parent::render($request, $e);
    }

    /**
     * Transform throwable to RPC exception.
     *
     * @param Throwable $e
     * @return RpcException
     */
    protected function transformThrowableToRPCException(Throwable $e): RpcException
    {
        $code = $this->extractCode($e);
        $message = $e->getMessage();
        switch (true) {
            case $e instanceof ModelNotFoundException:
                return RpcException::make($message, -404);
            case $e instanceof AuthorizationException:
                return RpcException::make($message, -403);
            case $e instanceof ValidationException:
                return InvalidParamsRpcException::make($e->errors());
            case 500 > $code && $code >= 400:
                return RpcException::make($message, -$code);
            default:
                return ServerErrorRpcException::make($message);
        }
    }

    /**
     * Extract code from throwable.
     *
     * @param Throwable $e
     * @return int
     */
    protected function extractCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        return $e->getCode();
    }
}
