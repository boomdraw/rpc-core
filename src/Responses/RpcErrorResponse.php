<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Responses;

class RpcErrorResponse extends RpcBaseResponse
{
    /**
     * RpcErrorResponse constructor.
     *
     * @param int $code
     * @param string $message
     * @param mixed $data
     * @param bool $id
     */
    public function __construct(int $code, string $message = '', $data = null, bool $id = true)
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'id' => $id ? context('requestId') : null,
        ];
        if (null !== $data) {
            $response['error']['data'] = $data;
        }

        parent::__construct($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
