<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Concerns;

use Boomdraw\RpcCore\Exceptions\MethodNotFoundRpcException;
use Boomdraw\RpcCore\Handler;
use Boomdraw\RpcCore\Request as RpcRequest;
use Boomdraw\RpcCore\Responses\RpcBaseResponse;
use Boomdraw\RpcCore\Responses\RpcEmptyResponse;
use Boomdraw\RpcCore\Responses\RpcResponse;
use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use SplFileInfo;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

trait RpcRequests
{
    /**
     * Boots the registered providers.
     */
    abstract public function boot();

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string $callback
     * @param array<string, mixed> $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    abstract public function call($callback, array $parameters = [], $defaultMethod = null);

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed $instance
     * @return mixed
     */
    abstract public function instance($abstract, $instance);

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    abstract public function make($abstract, array $parameters = []);

    /**
     * Gather the full class names for the middleware short-cut string.
     *
     * @param mixed $middleware
     * @return array
     */
    abstract protected function gatherMiddlewareClassNames($middleware);

    /**
     * Prepare the given request instance for use with the application.
     *
     * @param SymfonyRequest $request
     * @return Request
     */
    abstract protected function prepareRequest(SymfonyRequest $request);

    /**
     * Send the exception to the handler and return the response.
     *
     * @param Throwable $e
     * @return SymfonyResponse
     */
    abstract protected function sendExceptionToHandler(Throwable $e);

    /**
     * Send the request through the pipeline with the given callback.
     *
     * @param array $middleware
     * @param Closure $then
     * @return mixed
     */
    abstract protected function sendThroughPipeline(array $middleware, Closure $then);

    /**
     * Dispatch the incoming request.
     *
     * @param SymfonyRequest|null $request
     * @return SymfonyResponse
     * @throws BadRequestException
     */
    public function rpcDispatch(?SymfonyRequest $request = null): SymfonyResponse
    {
        try {
            if ($request) {
                $request = RpcRequest::createFromBase($request);
            }
            $method = $this->parseIncomingRpcRequest($request);
            $this->boot();

            return $this->sendThroughPipeline($this->middleware, function (Request $request) use ($method) {
                $this->instance(Request::class, $request);

                return $this->proceedHandler($method);
            });
        } catch (Throwable $e) {
            return $this->prepareRpcResponse($this->sendExceptionToHandler($e));
        }
    }

    /**
     * Parse the incoming request and return the method and path info.
     *
     * @param RpcRequest|null $request
     * @return string
     */
    public function parseIncomingRpcRequest(?RpcRequest $request = null): string
    {
        if (! $request) {
            $request = RpcRequest::capture();
        }

        $this->instance(Request::class, $this->prepareRequest($request));

        return $request->getRpcMethod();
    }

    /**
     * Prepare and call handler.
     *
     * @param string $method
     * @return SymfonyResponse|mixed
     * @throws MethodNotFoundRpcException
     */
    public function proceedHandler(string $method)
    {
        [$class, $method] = $this->extractCallable($method);
        if (empty($method) || ! class_exists($class) || ! method_exists($instance = $this->make($class), $method)) {
            throw new MethodNotFoundRpcException();
        }
        if ($instance instanceof Handler) {
            return $this->callCoreHandler($instance, $method);
        }

        return $this->callHandlerCallable([$instance, $method]);
    }

    /**
     * Extract instance and method.
     *
     * @param string $method
     * @return array
     */
    protected function extractCallable(string $method): array
    {
        $callable = explode('.', $method, 2);

        $class = $this->getHandlerClass($callable[0]);
        $method = $callable[1] ?? '__invoke';

        return [$class, $method];
    }

    protected function getHandlerClass(string $handler): string
    {
        $handler = Str::studly($handler);
        $namespace = config('rpc.handler.path', 'App\Rpc\Handlers');
        $namespace = trim($namespace, " \t\n\r\0\x0B\\");
        $suffix = config('rpc.handler.suffix', 'Handler');
        $suffix = trim($suffix, " \t\n\r\0\x0B\\");

        $class = $namespace.'\\'.$handler.$suffix;
        $class = config("rpc.handlers.$handler", $class);

        return $class;
    }

    /**
     * Send the request through a Lumen handler.
     *
     * @param mixed $instance
     * @param string $method
     * @return mixed
     */
    protected function callCoreHandler($instance, $method)
    {
        $middleware = $instance->getMiddlewareForMethod($method);

        if (count($middleware) > 0) {
            return $this->callCoreHandlerWithMiddleware(
                $instance, $method, $middleware
            );
        }

        return $this->callHandlerCallable([$instance, $method]);
    }

    /**
     * Send the request through a set of handler middleware.
     *
     * @param mixed $instance
     * @param string $method
     * @param array $middleware
     * @return mixed
     */
    protected function callCoreHandlerWithMiddleware($instance, $method, $middleware)
    {
        /** @psalm-suppress InvalidArgument */
        $middleware = $this->gatherMiddlewareClassNames($middleware);

        return $this->sendThroughPipeline($middleware, function () use ($instance, $method) {
            return $this->callHandlerCallable([$instance, $method]);
        });
    }

    /**
     * Call a handler callable and return the response.
     *
     * @param callable $callable
     * @return SymfonyResponse
     */
    protected function callHandlerCallable(callable $callable): SymfonyResponse
    {
        return $this->prepareRpcResponse(
            $this->call($callable, [])
        );
    }

    /**
     * Prepare the response for sending.
     *
     * @param mixed $response
     * @return Response|BinaryFileResponse|SymfonyResponse|RpcResponse
     */
    public function prepareRpcResponse($response): SymfonyResponse
    {
        $request = app(Request::class);
        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        } elseif ($response instanceof PsrResponseInterface) {
            $response = (new HttpFoundationFactory)->createResponse($response);
        } elseif ($response instanceof SplFileInfo) {
            $response = new BinaryFileResponse($response);
        }
        if ($response instanceof BinaryFileResponse) {
            return $response->prepare(Request::capture());
        }
        if (! $response instanceof RpcBaseResponse) {
            $response = $this->transformResponseToRpc($response);
        }

        return $response->prepare($request);
    }

    /**
     * Transform raw content or Symfony response to RPC response.
     *
     * @param $response
     * @return RpcBaseResponse
     */
    protected function transformResponseToRpc($response): RpcBaseResponse
    {
        if ($response instanceof SymfonyResponse) {
            $response = $response->getContent() ?: null;
        }
        if (null === $response) {
            return new RpcEmptyResponse();
        }

        return new RpcResponse($response);
    }
}
