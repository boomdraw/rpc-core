<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Concerns;

use Boomdraw\RpcCore\Request as RpcRequest;
use Laravel\Lumen\Concerns\RoutesRequests;
use Laravel\Lumen\Http\Request as LumenRequest;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RequestManager
{
    use RpcRequests {
        rpcDispatch as rpcDispatch;
    }
    use RoutesRequests {
        dispatch as routesDispatch;
    }

    /**
     * Dispatch the incoming request.
     *
     * @param SymfonyRequest|null $request
     * @return Response
     * @throws NotFoundHttpException|SuspiciousOperationException
     * @psalm-suppress LessSpecificImplementedReturnType
     */
    public function dispatch($request = null): Response
    {
        if (! $request) {
            $request = SymfonyRequest::createFromGlobals();
        }

        if ($this->isRpcRequest($request)) {
            return $this->rpcDispatch($request);
        }

        $routesEnabled = config('app.routes', true);
        if ($routesEnabled) {
            $request = LumenRequest::createFromBase($request);

            return $this->routesDispatch($request);
        }

        throw new NotFoundHttpException();
    }

    /**
     * Determine the request matches JsonRPC specification.
     *
     * @param SymfonyRequest $request
     * @return bool
     * @throws SuspiciousOperationException
     */
    protected function isRpcRequest(SymfonyRequest $request): bool
    {
        $rpc = config('app.rpc', true);
        if (! $rpc) {
            return false;
        }
        if ($request instanceof RpcRequest) {
            return true;
        }
        $method = $request->getMethod();
        $pathInfo = trim($request->getPathInfo(), '/');
        if (is_string($rpc)) {
            $rpcPath = trim($rpc, " \t\n\r\0\x0B/\\");
        } else {
            $rpcPath = 'v'.config('app.version', '1.0');
        }

        return 'POST' === $method && $rpcPath === $pathInfo;
    }
}
