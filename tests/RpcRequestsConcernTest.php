<?php

namespace Boomdraw\RpcCore\Tests;

use App\Rpc\Handlers\CustomHandler;
use App\Rpc\TestMiddleware;
use Boomdraw\RpcCore\Exceptions\ParseErrorRpcException;

class RpcRequestsConcernTest extends TestCase
{
    /**
     * @test
     */
    public function it_calls_handlers_from_custom_namespaces(): void
    {
        $this->send('Custom');
        self::assertEquals(
            'App\Rpc\Handlers',
            $this->getRpcData()
        );
        config(['rpc.handler.path' => 'App\Rpc']);
        $this->send('Custom');
        self::assertEquals(
            'App\\Rpc',
            $this->getRpcData()
        );
        config(['rpc.handlers.Blah' => CustomHandler::class]);
        $this->send('Blah');
        self::assertEquals(
            'App\Rpc\Handlers',
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_calls_custom_callable(): void
    {
        $this->send('CustomCallable.get');
        self::assertEquals(
            'callable',
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_calls_handler_with_global_middleware(): void
    {
        $this->app->middleware([TestMiddleware::class]);

        $this->send('Example');
        self::assertEquals(
            TestMiddleware::class,
            $this->getRpcData()
        );

        $this->send('CustomCallable.get');
        self::assertEquals(
            TestMiddleware::class,
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_calls_handler_with_middleware(): void
    {
        $this->send('Example.withMiddleware');
        self::assertEquals(
            TestMiddleware::class,
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_captures_prc_request_if_not_provided(): void
    {
        $this->expectException(ParseErrorRpcException::class);
        $this->app->parseIncomingRpcRequest();
    }

    /**
     * @test
     */
    public function it_throws_exception_whe_method_does_not_exists(): void
    {
        //$this->expectException(MethodNotFoundRpcException::class);
        $this->send('Custom.hello');
        $this->seeJsonRpcError([
            'code' => -32601,
            'message' => 'Method not found',
        ]);
    }

    /**
     * @test
     */
    public function it_throws_exception_whe_method_handler_does_not_exists(): void
    {
        $this->send('Custom2');
        $this->seeJsonRpcError([
            'code' => -32601,
            'message' => 'Method not found',
        ]);
    }
}
