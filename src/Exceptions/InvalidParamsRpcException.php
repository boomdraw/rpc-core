<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

use Illuminate\Contracts\Validation\Validator;

class InvalidParamsRpcException extends RpcException
{
    /**
     * InvalidParamsRpcException constructor.
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $message = 'Invalid Params';
        if ($data instanceof Validator) {
            $data = $data->errors()->getMessages();
        }
        parent::__construct($message, self::INVALID_PARAMS_ERROR_CODE, $data);
    }
}
