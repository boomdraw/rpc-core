<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RpcTrait;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/lumen/bootstrap/app.php';
    }
}
