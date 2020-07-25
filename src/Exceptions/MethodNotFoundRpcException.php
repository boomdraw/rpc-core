<?php

namespace Boomdraw\RpcCore\Exceptions;

class MethodNotFoundRpcException extends RpcException
{
    public function __construct($message = 'Method not found', $data = null)
    {
        parent::__construct($message, -32601, $data);
    }
}
