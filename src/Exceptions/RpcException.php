<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Exceptions;

use Boomdraw\RpcCore\Responses\RpcErrorResponse;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;

class RpcException extends Exception implements Responsable
{
    public const SERVER_ERROR_CODE = -3200; //-32000 to -32099
    public const INVALID_PARAMS_ERROR_CODE = -32602;
    public const INVALID_REQUEST_ERROR_CODE = -32600;
    public const METHOD_NOT_FOUND_ERROR_CODE = -32601;
    public const PARSE_ERROR_CODE = -32700;

    /** @var bool */
    protected bool $id;

    /** @var mixed */
    protected $data;

    /**
     * RpcException constructor.
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @param bool $id
     */
    public function __construct(string $message = '', int $code = 0, $data = null, $id = true)
    {
        $this->id = $id;
        $this->data = $data;
        parent::__construct($message, $code);
    }

    /**
     * @param mixed ...$args
     * @return RpcException
     */
    public static function make(...$args): self
    {
        return new static(...$args);
    }

    /**
     * @param Request $request
     * @return RpcErrorResponse
     */
    public function toResponse($request): RpcErrorResponse
    {
        return new RpcErrorResponse($this->getCode(), $this->getMessage(), $this->data, $this->id);
    }
}
