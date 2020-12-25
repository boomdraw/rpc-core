<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Responses;

class RpcEmptyResponse extends RpcBaseResponse
{
    public function __construct()
    {
        parent::__construct(null, 204, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
