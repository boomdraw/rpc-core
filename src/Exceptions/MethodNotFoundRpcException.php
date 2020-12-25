<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

class MethodNotFoundRpcException extends RpcException
{
    /**
     * MethodNotFoundRpcException constructor.
     *
     * @param string $message
     * @param mixed $data
     */
    public function __construct($message = 'Method not found', $data = null)
    {
        parent::__construct($message, self::METHOD_NOT_FOUND_ERROR_CODE, $data);
    }
}
