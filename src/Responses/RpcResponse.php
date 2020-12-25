<?php

declare(strict_types=1);

namespace Boomdraw\RpcCore\Responses;

use Throwable;

class RpcResponse extends RpcBaseResponse
{
    /**
     * RpcResponse constructor.
     *
     * @param mixed $content
     */
    public function __construct($content = '')
    {
        $content = $this->transformContentToArray($content);
        $content = $this->wrapContent($content);
        $content = $this->structureContent($content);

        parent::__construct($content, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Transform content to array.
     *
     * @param mixed $content
     * @return mixed[]
     */
    protected function transformContentToArray($content = ''): array
    {
        if (is_array($content)) {
            return $content;
        }
        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            return ['data' => $content];
        }
    }

    /**
     * Structure content according to JsonRPC specification.
     *
     * @param array $data
     * @return array
     */
    protected function structureContent(array $data): array
    {
        return [
            'jsonrpc' => '2.0',
            'result' => $data,
            'id' => context('requestId'),
        ];
    }

    /**
     * Wrap content.
     *
     * @param array $data
     * @return array
     */
    protected function wrapContent(array $data): array
    {
        if (! array_key_exists('data', $data)) {
            return compact('data');
        }

        return $data;
    }
}
