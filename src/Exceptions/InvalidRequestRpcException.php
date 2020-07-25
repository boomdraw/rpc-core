<?php

namespace Boomdraw\RpcCore\Exceptions;

class InvalidRequestRpcException extends RpcException
{
    public function __construct($message = 'Invalid Request')
    {
        parent::__construct($message, -32600, null, false);
    }
}
