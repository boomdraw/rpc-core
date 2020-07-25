<?php

namespace App\Rpc;

use Boomdraw\RpcCore\Responses\RpcResponse;
use Closure;
use Illuminate\Http\Request;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return new RpcResponse(__CLASS__);
    }
}
