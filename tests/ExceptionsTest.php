<?php

namespace Boomdraw\RpcCore\Tests;

use Boomdraw\RpcCore\Responses\RpcErrorResponse;

class ExceptionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_server_error_exception(): void
    {
        $this->send('Example.throwException', ['exception' => 500]);
        $this->seeJsonRpcError(['code' => -3200]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_throws_method_not_found_exception(): void
    {
        $this->send('Example.throw', ['exception' => 500]);
        $this->seeJsonRpcError(['code' => -32601]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_throws_invalid_request_exception(): void
    {
        $this->post('/v1.0', ['blablabla']);
        $this->seeJsonRpcError(['code' => -32600]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_throws_parse_error_exception(): void
    {
        $this->post('/v1.0');
        $this->seeJsonRpcError(['code' => -32700]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_throws_invalid_params_exception(): void
    {
        $this->send('Example.throwValidatorException');
        $this->seeJsonRpcError([
            'code' => -32602,
            'data' => ['hello' => ['The hello field is required.']],
        ]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();

        $this->send('Example.throwValidatorInnerException');
        $this->seeJsonRpcError([
            'code' => -32602,
            'data' => ['hello' => ['The hello field is required.']],
        ]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();

        $this->send('Example.throwInvalidParamsException');
        $this->seeJsonRpcError([
            'code' => -32602,
            'data' => ['hello' => ['The hello field is required.']],
        ]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_handles_non_rpc_exceptions(): void
    {
        $this->get('/exception');
        $this->assertResponseStatus(500);
    }

    /**
     * @test
     */
    public function it_handles_authorization_exception(): void
    {
        $this->send('Example.throwAuthorizationException');
        $this->seeJsonRpcError(['code' => -403]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_handles_model_not_found_exception(): void
    {
        $this->send('Example.throwModelNotFoundException');
        $this->seeJsonRpcError(['code' => -404]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function it_handles_bad_request_exceptions(): void
    {
        $this->send('Example.throwException', ['exception' => 400]);
        $this->seeJsonRpcError(['code' => -400]);
        $this->assertResponseInstanceOf(RpcErrorResponse::class);
        $this->assertResponseOk();
    }
}
