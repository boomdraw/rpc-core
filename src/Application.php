<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore;

use Boomdraw\RpcCore\Concerns\RequestManager;
use Laravel\Lumen\Application as BaseApplication;

class Application extends BaseApplication
{
    use RequestManager;
}
