<?php

namespace App\Rpc\Handlers;

use App\Models\TestModel;
use App\Rpc\TestMiddleware;
use Boomdraw\RpcCore\Exceptions\InvalidParamsRpcException;
use Boomdraw\RpcCore\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExampleHandler extends Handler
{
    public function __construct()
    {
        $this->middleware(TestMiddleware::class, ['only' => ['withMiddleware']]);
    }

    public function __invoke()
    {
        return app()->version();
    }

    public function withMiddleware()
    {
        return app()->version();
    }

    public function unwrappedData()
    {
        return ['hello' => 'world'];
    }

    public function wrappedData()
    {
        return ['data' => ['hello' => 'world']];
    }

    public function emptyData()
    {
    }

    public function throwException(Request $request)
    {
        abort($request->exception);
    }

    public function throwValidatorException(Request $request)
    {
        throw ValidationException::withMessages(['hello' => ['The hello field is required.']]);
    }

    public function throwValidatorInnerException(Request $request)
    {
        $this->validate($request, ['hello' => ['required']]);
    }

    public function throwInvalidParamsException(Request $request)
    {
        $validator = $this->getValidationFactory()->make($request->all(), ['hello' => ['required']]);
        throw new InvalidParamsRpcException($validator);
    }

    public function throwModelNotFoundException(Request $request)
    {
        throw new ModelNotFoundException(new TestModel());
    }

    public function throwAuthorizationException(Request $request)
    {
        throw new AuthorizationException();
    }
}
