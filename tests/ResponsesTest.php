<?php

namespace Boomdraw\RpcCore\Tests;

use Boomdraw\RpcCore\Responses\RpcEmptyResponse;
use Boomdraw\RpcCore\Responses\RpcResponse;

class ResponsesTest extends TestCase
{
    /**
     * @test
     */
    public function it_wraps_response_data(): void
    {
        $this->send('Example.unwrappedData');
        $response = $this->responseAsArray();
        self::assertEquals(['data' => ['hello' => 'world']], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_does_not_wraps_wrapped_data(): void
    {
        $this->send('Example.wrappedData');
        $response = $this->responseAsArray();
        self::assertEquals(['data' => ['hello' => 'world']], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_sends_empty_response(): void
    {
        $this->send('Example.emptyData');
        $this->assertResponseStatus(204);
        $this->assertResponseInstanceOf(RpcEmptyResponse::class);
    }
}
