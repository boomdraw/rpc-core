<?php

if (! function_exists('context')) {
    /**
     * Get RPC request context.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function context(string $key, $default = null)
    {
        $request = app('request');
        if (! $request instanceof Boomdraw\RpcCore\Request) {
            return $default;
        }

        return $request->context($key, $default);
    }
}
