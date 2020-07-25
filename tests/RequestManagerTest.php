<?php

namespace Boomdraw\RpcCore\Tests;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_passes_requests_to_routes(): void
    {
        $this->get('/');
        self::assertEquals(
            $this->app->version(),
            $this->response->getContent()
        );
    }

    /**
     * @test
     */
    public function it_catches_rpc_requests(): void
    {
        $this->post('/v1.0', [
            'jsonrpc' => '2.0',
            'method' => 'Example',
            'id' => 19,
        ]);

        self::assertEquals(
            $this->app->version(),
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_catches_rpc_requests_for_specific_application_version(): void
    {
        config(['app.version' => '2.1']);
        $this->post('/v2.1', [
            'jsonrpc' => '2.0',
            'method' => 'Example',
            'params' => [],
            'id' => 19,
        ]);

        self::assertEquals(
            $this->app->version(),
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_catches_rpc_requests_when_custom_path_provided(): void
    {
        config(['app.rpc' => '/rpc']);
        $this->post('/rpc', [
            'jsonrpc' => '2.0',
            'method' => 'Example',
            'params' => [],
            'id' => 19,
        ]);

        self::assertEquals(
            $this->app->version(),
            $this->getRpcData()
        );
    }

    /**
     * @test
     */
    public function it_creates_request_when_not_provided(): void
    {
        self::assertEquals(
            $this->app->version(),
            $this->app->dispatch()->getContent()
        );
    }

    /**
     * @test
     */
    public function it_ignores_routes_when_disabled(): void
    {
        $this->expectException(NotFoundHttpException::class);
        config(['app.routes' => false]);
        $this->get('/');
    }

    /**
     * @test
     */
    public function it_ignores_rpc_when_disabled(): void
    {
        $this->expectException(NotFoundHttpException::class);
        config(['app.routes' => false]);
        config(['app.rpc' => false]);
        $this->post('/v1.0');
        self::assertEquals(
            $this->app->version(),
            $this->response->getContent()
        );
    }
}
