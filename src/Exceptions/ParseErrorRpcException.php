<?php

namespace Boomdraw\RpcCore\Exceptions;

class ParseErrorRpcException extends RpcException
{
    public function __construct($message = 'Parse error')
    {
        parent::__construct($message, -32700, null, false);
    }
}
