<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

class ServerErrorRpcException extends RpcException
{
    /**
     * ServerErrorRpcException constructor.
     *
     * @param string $message
     * @param mixed $data
     */
    public function __construct($message = 'Server Error', $data = null)
    {
        parent::__construct($message, self::SERVER_ERROR_CODE, $data);
    }
}
