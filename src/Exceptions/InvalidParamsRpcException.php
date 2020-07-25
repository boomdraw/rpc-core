<?php

namespace Boomdraw\RpcCore\Exceptions;

use Illuminate\Contracts\Validation\Validator;

class InvalidParamsRpcException extends RpcException
{
    public function __construct($data = null)
    {
        $message = 'Invalid Params';
        if ($data instanceof Validator) {
            $data = $data->errors()->getMessages();
        }
        parent::__construct($message, -32602, $data);
    }
}
