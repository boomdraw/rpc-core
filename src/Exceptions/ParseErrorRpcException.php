<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

class ParseErrorRpcException extends RpcException
{
    /**
     * ParseErrorRpcException constructor.
     *
     * @param string $message
     */
    public function __construct($message = 'Parse error')
    {
        parent::__construct($message, self::PARSE_ERROR_CODE, null, false);
    }
}
