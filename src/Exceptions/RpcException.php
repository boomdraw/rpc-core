<?php

namespace Boomdraw\RpcCore\Exceptions;

use Boomdraw\RpcCore\Responses\RpcErrorResponse;
use Exception;
use Illuminate\Contracts\Support\Responsable;

class RpcException extends Exception implements Responsable
{
    protected $id = true;

    protected $data = null;

    public function __construct(string $message = '', int $code = 0, $data = null, $id = true)
    {
        $this->id = $id;
        $this->data = $data;
        parent::__construct($message, $code);
    }

    public static function make(...$args)
    {
        return new static(...$args);
    }

    public function toResponse($request)
    {
        return new RpcErrorResponse($this->getCode(), $this->getMessage(), $this->data, $this->id);
    }
}
