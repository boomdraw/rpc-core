<?php

namespace Boomdraw\RpcCore;

use Boomdraw\RpcCore\Exceptions\InvalidRequestRpcException;
use Boomdraw\RpcCore\Exceptions\ParseErrorRpcException;
use Illuminate\Http\Request as BaseRequest;
use JsonException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Throwable;

class Request extends BaseRequest
{
    /** @var string called rpc method. */
    protected string $rpcMethod;

    /** @var ContextBag provided context. */
    protected ContextBag $context;

    /**
     * Request constructor.
     *
     * @param string $rpcMethod
     * @param array $request
     * @param array $server
     * @param string|resource|null $content
     * @param array $context
     */
    public function __construct(string $rpcMethod, array $request = [], array $server = [], $content = null, array $context = [])
    {
        parent::__construct([], $request, [], [], [], $server, $content);

        $this->rpcMethod = $rpcMethod;
        $this->context = new ContextBag($context);
    }

    /**
     * Create an RPC request from a Symfony instance.
     *
     * @param SymfonyRequest $request
     * @return static
     * @throws InvalidRequestRpcException
     * @throws ParseErrorRpcException|JsonException
     */
    public static function createFromBase(SymfonyRequest $request)
    {
        if ($request instanceof self) {
            return $request;
        }

        $input = self::getInput($request);
        $jsonrpc = data_get($input, 'jsonrpc');
        $method = data_get($input, 'method');
        if (! is_object($input) || ! $method || is_numeric($method[0]) || $jsonrpc !== '2.0') {
            throw new InvalidRequestRpcException();
        }

        $params = data_get($input, 'params', []);
        $args = data_get($params, 'args', []);
        $context = (array) data_get($params, 'context', []);
        if (empty($args) && empty($context)) {
            $args = $params;
        }
        data_set($context, 'requestId', data_get($input, 'id'));

        return new static($method, (array) $args, self::getServer(), json_encode($args, JSON_THROW_ON_ERROR), $context);
    }

    /**
     * Return rpc method name.
     *
     * @return string
     */
    public function getRpcMethod(): string
    {
        return $this->rpcMethod;
    }

    /**
     * Retrieve a context item from the request.
     *
     * @param string|null $key
     * @param mixed $default
     * @return string|array|null
     */
    public function context(?string $key = null, $default = null)
    {
        return $this->retrieveItem('context', $key, $default);
    }

    /**
     * Determine if a context item is set on the request.
     *
     * @param string $key
     * @return bool
     */
    public function hasContext(string $key): bool
    {
        return ! is_null($this->context($key));
    }

    /**
     * Return request input.
     *
     * @return object
     * @throws ParseErrorRpcException
     */
    protected static function getInput(SymfonyRequest $request): object
    {
        $input = $request->request->all();
        if (! empty($input)) {
            return (object) $input;
        }

        $input = file_get_contents('php://input');
        try {
            $input = json_decode($input, false, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new ParseErrorRpcException();
        }

        return $input;
    }

    /**
     * Return $_SERVER variables with forced content type and accept headers.
     *
     * @return array
     */
    protected static function getServer(): array
    {
        $server = $_SERVER;
        $server['HTTP_CONTENT_TYPE'] = $server['CONTENT_TYPE'] = $server['HTTP_ACCEPT'] = 'application/json';

        return $server;
    }
}
