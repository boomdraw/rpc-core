<?php

declare(strict_types=1);

namespace App\Rpc\Handlers;

use Boomdraw\RpcCore\Handler;

class CustomHandler extends Handler
{
    public function __invoke()
    {
        return __NAMESPACE__;
    }
}
