<?php

namespace Boomdraw\RpcCore\Responses;

class RpcErrorResponse extends RpcBaseResponse
{
    public function __construct($code, $message, $data = null, $id = true)
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
