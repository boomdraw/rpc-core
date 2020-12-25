<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

class InvalidRequestRpcException extends RpcException
{
    /**
     * InvalidRequestRpcException constructor.
     *
     * @param string $message
     */
    public function __construct($message = 'Invalid Request')
    {
        parent::__construct($message, self::INVALID_REQUEST_ERROR_CODE, null, false);
    }
}
