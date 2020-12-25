<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Tests;

use ArrayAccess;
use Boomdraw\RpcCore\Exceptions\InvalidRequestRpcException;
use Boomdraw\RpcCore\Exceptions\ParseErrorRpcException;
use Boomdraw\RpcCore\Request as RpcRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use JsonException;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\Request as LumenRequest;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait RpcTrait
{
    use MakesHttpRequests;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * The last response returned by the application.
     *
     * @var TestResponse|Response
     */
    protected $response;

    /**
     * Call the given URI and return the Response.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @return TestResponse|Response|SymfonyResponse
     * @throws InvalidRequestRpcException|ParseErrorRpcException|JsonException
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->currentUri = $this->prepareUrlForRequest($uri);

        $rpc = false;
        if ($method === 'SEND') {
            $method = 'POST';
            $rpc = true;
        }

        $symfonyRequest = SymfonyRequest::create(
            $this->currentUri, $method, $parameters,
            $cookies, $files, $server, $content
        );

        if ($rpc) {
            $this->app['request'] = RpcRequest::createFromBase($symfonyRequest);
        } else {
            $this->app['request'] = LumenRequest::createFromBase($symfonyRequest);
        }

        $response = $this->app->prepareResponse($this->app->handle($this->app['request']));

        if (class_exists('Illuminate\Testing\TestResponse')) {
            return $this->response = TestResponse::fromBaseResponse($response);
        }

        return $this->response = $response;
    }

    /**
     * Call the given method with a RPC request.
     *
     * @param string $method
     * @param array $args
     * @param array $context
     * @return mixed
     * @throws InvalidRequestRpcException|ParseErrorRpcException|JsonException
     */
    public function send(string $method, array $args = [], array $context = [])
    {
        $uri = config('app.rpc', null);
        if (! is_string($uri)) {
            $uri = '/v'.config('app.version', '1.0');
        }
        $data = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => compact('args', 'context'),
            'id' => uniqid('', true),
        ];

        return $this->call('SEND', $uri, $data, [], [], []);
    }

    /**
     * Extract data from RPC response.
     *
     * @param string $key
     * @return array|ArrayAccess|mixed
     */
    public function getRpcData(string $key = '')
    {
        $actual = $this->responseAsArray();
        if (! empty($key)) {
            $key = ".$key";
        }
        $key = 'result.data'.$key;

        return Arr::get($actual, $key);
    }

    /**
     * Return Response content as an array.
     *
     * @return array
     */
    public function responseAsArray(): array
    {
        $actual = json_decode($this->response->getContent(), true);
        if (false === $actual || null === $actual) {
            Assert::fail('Invalid JSON was returned from the route. Perhaps an exception was thrown?');
        }

        return $actual;
    }

    /**
     * Assert that the response contains provided data.
     *
     * @param array $data
     * @return $this
     * @throws JsonException
     */
    public function seeJsonRpc(array $data = []): self
    {
        if (empty($data)) {
            return $this;
        }

        $actual = $this->responseAsArray();
        Assert::assertSame($actual['jsonrpc'], '2.0');
        Assert::assertTrue(array_key_exists('result', $actual));
        Assert::assertTrue(array_key_exists('id', $actual));

        $actual = Arr::sortRecursive((array) Arr::get($actual, 'result.data'));
        $actual = json_encode($actual, JSON_THROW_ON_ERROR);
        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);
            Assert::assertTrue(
                Str::contains($actual, $expected),
                "Unable to find JSON fragment [{$expected}] within [{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Assert that the error response contains provided data.
     *
     * @param array $data
     * @return $this
     * @throws JsonException
     */
    public function seeJsonRpcError(array $data = []): self
    {
        if (empty($data)) {
            return $this;
        }

        $actual = $this->responseAsArray();
        Assert::assertSame($actual['jsonrpc'], '2.0');
        Assert::assertTrue(array_key_exists('error', $actual));
        Assert::assertTrue(array_key_exists('id', $actual));

        $actual = Arr::sortRecursive((array) Arr::get($actual, 'error'));
        $actual = json_encode($actual, JSON_THROW_ON_ERROR);
        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);
            Assert::assertTrue(
                Str::contains($actual, $expected),
                "Unable to find JSON fragment [{$expected}] within [{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that a response is of a given type.
     *
     * @param string $class
     */
    public function assertResponseInstanceOf(string $class): void
    {
        $response = $this->response;
        $testResponseClass = 'Illuminate\Testing\TestResponse';
        if ($response instanceof $testResponseClass) {
            self::assertInstanceOf($class, $response->baseResponse);
        } else {
            self::assertInstanceOf($class, $response);
        }
    }
}
