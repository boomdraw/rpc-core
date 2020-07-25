<?php

namespace Boomdraw\RpcCore\Exceptions;

class ServerErrorRpcException extends RpcException
{
    public function __construct($message = 'Server Error', $data = null)
    {
        parent::__construct($message, -32000, $data); //-32000 to -32099
    }
}
