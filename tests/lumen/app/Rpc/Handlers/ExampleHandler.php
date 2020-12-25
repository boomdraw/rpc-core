<?php

declare(strict_types=1);

namespace App\Rpc\Handlers;

use App\Models\TestModel;
use App\Rpc\TestMiddleware;
use Boomdraw\RpcCore\Exceptions\InvalidParamsRpcException;
use Boomdraw\RpcCore\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class ExampleHandler extends Handler
{
    public function __construct()
    {
        $this->middleware(TestMiddleware::class, ['only' => ['withMiddleware']]);
    }

    public function __invoke(): string
    {
        return app()->version();
    }

    public function withMiddleware(): string
    {
        return app()->version();
    }

    public function booleanData(): bool
    {
        return false;
    }

    public function integerData(): int
    {
        return 0;
    }

    public function unwrappedData(): array
    {
        return ['hello' => 'world'];
    }

    public function wrappedData(): array
    {
        return ['data' => ['hello' => 'world']];
    }

    public function responsableData(): Responsable
    {
        return new JsonResource(['hello' => 'world']);
    }

    public function emptyArrayData(): array
    {
        return [];
    }

    public function emptyStringData(): string
    {
        return '';
    }

    public function emptyData(): void
    {
    }

    public function binaryFileData(): SplFileInfo
    {
        $file = storage_path('testfile');

        return new SplFileInfo($file);
    }

    public function psrResponseData(): ResponseInterface
    {
        $response = new Response(['data' => ['hello' => 'world']]);

        return app(PsrHttpFactory::class)->createResponse($response);
    }

    public function throwException(Request $request): void
    {
        abort($request->exception);
    }

    public function throwValidatorException(): void
    {
        throw ValidationException::withMessages(['hello' => ['The hello field is required.']]);
    }

    public function throwValidatorInnerException(Request $request): void
    {
        $this->validate($request, ['hello' => ['required']]);
    }

    public function throwInvalidParamsException(Request $request): void
    {
        $validator = $this->getValidationFactory()->make($request->all(), ['hello' => ['required']]);
        throw new InvalidParamsRpcException($validator);
    }

    public function throwModelNotFoundException(): void
    {
        throw (new ModelNotFoundException())->setModel(new TestModel());
    }

    public function throwAuthorizationException(): void
    {
        throw new AuthorizationException();
    }
}
