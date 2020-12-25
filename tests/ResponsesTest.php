<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Tests;

use Boomdraw\RpcCore\Responses\RpcEmptyResponse;
use Boomdraw\RpcCore\Responses\RpcResponse;
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UploadedFileFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResponsesTest extends TestCase
{
    /**
     * @test
     */
    public function it_wraps_response_data(): void
    {
        $this->send('Example.unwrappedData');
        $this->assertResponseOk();
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
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => ['hello' => 'world']], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_responsable_data(): void
    {
        $this->send('Example.responsableData');
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => ['hello' => 'world']], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_boolean_data(): void
    {
        $this->send('Example.booleanData');
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => false], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_integer_data(): void
    {
        $this->send('Example.booleanData');
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => 0], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_empty_array_data(): void
    {
        $this->send('Example.emptyArrayData');
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => []], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_empty_string_data(): void
    {
        $this->send('Example.emptyStringData');
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => ''], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_empty_data(): void
    {
        $this->send('Example.emptyData');
        $this->assertResponseStatus(204);
        $this->assertResponseInstanceOf(RpcEmptyResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_binary_file_data(): void
    {
        $this->send('Example.binaryFileData');
        $this->assertResponseOk();
        $this->assertResponseInstanceOf(BinaryFileResponse::class);
    }

    /**
     * @test
     */
    public function it_proceeds_psr_response_data(): void
    {
        $this->app->bind(ServerRequestFactoryInterface::class, ServerRequestFactory::class);
        $this->app->bind(StreamFactoryInterface::class, StreamFactory::class);
        $this->app->bind(UploadedFileFactoryInterface::class, UploadedFileFactory::class);
        $this->app->bind(ResponseFactoryInterface::class, ResponseFactory::class);

        $this->send('Example.psrResponseData');
        $this->assertResponseOk();
        $response = $this->responseAsArray();
        self::assertEquals(['data' => ['hello' => 'world']], $response['result']);
        $this->assertResponseInstanceOf(RpcResponse::class);
    }
}
