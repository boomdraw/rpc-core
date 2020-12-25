<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Tests;

class RequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_context_variables(): void
    {
        $this->send('Example', [], ['userId' => 1]);
        self::assertEquals(1, $this->app['request']->context('userId'));
        self::assertTrue($this->app['request']->hasContext('userId'));
        self::assertNull($this->app['request']->context('user'));
        self::assertFalse($this->app['request']->hasContext('user'));
    }

    /**
     * @test
     */
    public function it_throws_exception_when_invalid_jsonrpc_version_provided(): void
    {
        $this->post('/v1.0', [
            'jsonrpc' => '1.0',
            'method' => 'Example',
            'params' => [],
            'id' => 19,
        ]);
        $this->seeJsonRpcError(['code' => -32600]);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_method_is_not_provided(): void
    {
        $this->post('/v1.0', [
            'jsonrpc' => '2.0',
            'params' => [],
            'id' => 19,
        ]);
        $this->seeJsonRpcError(['code' => -32600]);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_empty_body_provided(): void
    {
        $this->post('/v1.0');
        $this->seeJsonRpcError(['code' => -32700]);
    }
}
