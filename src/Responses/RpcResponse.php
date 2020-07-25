<?php

namespace Boomdraw\RpcCore\Responses;

class RpcResponse extends RpcBaseResponse
{
    public function __construct($content = '')
    {
        $data = $content;
        if (! is_array($data)) {
            $data = json_decode($data, true);
            if (! is_array($data) || (null === $data && json_last_error())) {
                $data = ['data' => $content];
            }
        }
        if (! array_key_exists('data', $data)) {
            $data = compact('data');
        }
        $data = [
            'jsonrpc' => '2.0',
            'result' => $data,
            'id' => context('requestId'),
        ];

        parent::__construct($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
