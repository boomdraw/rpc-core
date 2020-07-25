# Lumen JSON-RPC core

Allows [Lumen](https://lumen.laravel.com/) to handle [JSON-RPC 2.0](https://www.jsonrpc.org/specification) requests and return [JSON-RPC 2.0](https://www.jsonrpc.org/specification) responses.

[![Build Status](https://img.shields.io/scrutinizer/build/g/boomdraw/rpc-core.svg?style=flat-square)](https://scrutinizer-ci.com/g/boomdraw/rpc-core)
[![StyleCI](https://github.styleci.io/repos/282461567/shield?branch=master)](https://github.styleci.io/repos/282461567)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/boomdraw/rpc-core.svg?style=flat-square)](https://scrutinizer-ci.com/g/boomdraw/rpc-core)
[![Quality Score](https://img.shields.io/scrutinizer/g/boomdraw/rpc-core.svg?style=flat-square)](https://scrutinizer-ci.com/g/boomdraw/rpc-core)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/boomdraw/rpc-core?style=flat-square)](https://packagist.org/packages/boomdraw/rpc-core)
[![Total Downloads](https://img.shields.io/packagist/dt/boomdraw/rpc-core.svg?style=flat-square)](https://packagist.org/packages/boomdraw/rpc-core)
[![PHP Version](https://img.shields.io/packagist/php-v/boomdraw/rpc-core?style=flat-square)](https://packagist.org/packages/boomdraw/rpc-core)
[![License](https://img.shields.io/packagist/l/boomdraw/rpc-core?style=flat-square?style=flat-square)](https://packagist.org/packages/boomdraw/rpc-core)

This package provides a request manager that passes the request to the RpcRequests or default RoutesRequests dispatcher.

## Version compatibility

 Lumen    | JSON-RPC core
:---------|:----------
 6.x      | 1.x
 7.x      | 1.x
 
## Installation

Via Composer

```bash
$ composer require boomdraw/rpc-core
```

Change the Application class in the bootstrap file to `Boomdraw\RpcCore\Application`
or provide your own Application class with `Boomdraw\RpcCore\Concerns\RequestManager` trait:

```php
// bootstrap/app.php

$app = new Boomdraw\RpcCore\Application(
    dirname(__DIR__)
);
```

Change exception handler:

```php
// bootstrap/app.php

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Boomdraw\RpcCore\Handler::class
);
```

Your Lumen application is ready to handle JSON-RPC requests!  

## Usage

### RPC endpoint

RPC concern handles only `POST` requests to the specific endpoint.

By default, it uses current application version `config('app.version', '1.0')` as a route path (`POST /v1.0`). 

You can provide a custom RPC path by setting it in the app config file:
```php
// config/app.php

return [
    ...
    'rpc' => 'my-custom-uri',
    ...
];
```

### Disable JSON-RPC requests

You can disable the RPC dispatcher in the `config/app.php` config file:

```php
// config/app.php

return [
    ...
    'rpc' => false,
    ...
];
```

### Disable routes requests

If you want to use your application as RPC only, you can disable default routes dispatcher in the `config/app.php` config file:

```php
// config/app.php

return [
    ...
    'routes' => false,
    ...
];
```

### JSON-RPC Handlers

Behind the scene, JSON-RPC handlers logic is the same as Controllers.
It works with global and routes middleware.
You can provide middleware for a handler or its specific method the same way as for [Controller](https://lumen.laravel.com/docs/7.x/controllers#controller-middleware).

The handler should extend `Boomdraw\RpcCore\Handler` class to have the same capabilities as the controller.

#### Handlers namespace and naming

By default, JSON-RPC requests concern will look for handlers with suffix `Handler` in the `App\Rpc\Handlers` namespace.
You can change handlers default namespace and/or suffix in the config file.

Create the file `config/rpc.php` with the next content:

```php
<?php

return [
    /*
    | Handlers default namespace and suffix.
    */
    'handler' => [
        'path' => 'App\Rpc\Handlers',
        'suffix' => 'Handler',
    ],
    /*
     | List of custom handlers with its paths.
     | Handler key must be in a studly caps case
     | Example:
     | 'CustomHelper' => App\Handlers\CustomRpcHandler::class
     */
    'handlers' => [
        //
    ],
];
```

or copy it from the vendor dir: 

```bash
cp vendor/boomdraw/rpc-core/config/rpc.php config/rpc.php
```

Register the config file in the bootstrap:

```php
// bootstrap/app.php

$app->configure('rpc');
```

Change `path` and `suffix` values in the config file as you wish.

#### Custom handlers

You can provide handlers with a custom name and namespace.

Register `rpc` config file as described in the step before.

Add to an array with key `handlers` of `config/rpc.php` file your handler using the name as a key and its class as a value.
The suffix will not be applied to this handler. 

You are awesome!

## Examples

### Handler example

```php
// app/Rpc/Handlers/ExampleHandler.php

<?php

namespace App\Rpc\Handlers;

use Boomdraw\RpcCore\Handler;
use Illuminate\Http\Request;

class ExampleHandler extends Handler
{
    public function __invoke(Request $request)
    {
        return ['data' => ['hello' => $request->hello]];
    }

    public function message(Request $request): string
    {
        return $request->message;
    }

    public function throwException(): void 
    {
        abort(500, 'Yeah!');
    }
}
```

### JSON-RPC request and response examples

`POST /v1.0`

```json
{
  "jsonrpc": "2.0",
  "method": "Example",
  "params": {"args": {"hello": "world"}, "context": {"userId":  11}},
  "id": 19
}
```

This request will call method `__invoke` in the `ExampleHandler`.
RPC response will not wrap returned result because it has been wrapped manually:

```json
{
  "jsonrpc": "2.0",
  "result": {
    "data": {"hello": "world"}
  },
  "id": 19
}
```


`POST /v1.0`

```json
{
  "jsonrpc": "2.0",
  "method": "Example.message",
  "params": {"message": "Hello world!"},
  "id": 19
}
```

This request will call method `message` in the `ExampleHandler`.
RPC response wraps returned result as an array with `data` as a key.
The `params` object will be passed to the `Request` instead of `args`, because `args` and `context` objects are not provided.

```json
{
  "jsonrpc": "2.0",
  "result": {
    "data": "Hello world!"
  },
  "id": 19
}
```


`POST /v1.0`

```json
{
  "jsonrpc": "2.0",
  "method": "Example.throwException",
  "id": 19
}
```

This request will call method `throwException` in the `ExampleHandler`.
RPC error response will be returned:

```json
{
  "jsonrpc": "2.0",
  "error": {
    "code": -32000,
    "message": "Yeah!"
  },
  "id": 19
}
```

You can read more about JSON-RPC requests and responses in the [official specification](https://www.jsonrpc.org/specification).

## Context

The data that have been passed to the `context` object in `params` will be stored in the request object.
You can get this data using `context` helper function.

```php
// function context(string $key, $default = null)

context('userId', 'default')
```

Result of this context call will be `userId` from `context` object
or string `default` if there is no `userId` field in the `context` or it is not a JSON-RPC request.

The JSON-RPC request id is always passed to the context.

Result of calling `context('requestId')` for previous examples will be `19`.

## Responses

When handler returns raw data, it will be wrapped to an array with `data` key if required and passed to
`Boomdraw\RpcCore\Responses\RpcResponse` for the response structure building.

If the handler returns `Illuminate\Http\Response` or `Symfony\Component\HttpFoundation\Response`
it will be transformed to the `Boomdraw\RpcCore\Responses\RpcResponse` with data wrapping and HTTP Status Code 200.

If the handlers does not return any data, the `Boomdraw\RpcCore\Responses\RpcEmptyResponse` will
be returned without payload and HTTP Status Code 204.

For non JSON-RPC request application will proceed the response by default way.

## Error responses

`RPC-core` package provides an exception handler that transforms exception regarding JSON-RPC specification.

All error responses will be returned with HTTP Status Code 200.

For the Server Error (code >= 500) will be returned error response with inner code `-32000`.

For the exceptions with code between 400 and 499 will be returned error response with the same code but with
negative sign before it. For example: for 405 error code will be returned response with inner code `-405`.
 
Validation exception will be transformed to `Boomdraw\RpcCore\Exceptions\InvalidParamsRpcException`
and returned with code `-32602`.

## Testing

### RPC helpers for testing

The package provides a class that makes JSON-RPC handlers testing much easier.

Add to your `TestCase.php` `Boomdraw\RpcCore\Tests\RpcTrait` trait.

Now you can use next methods:

#### send

`public function send(string $method, array $args = [], array $context = [])`

Transforms provided arguments to the JSON-RPC requests and passes it to the dispatcher.

#### getRpcData

`public function getRpcData(string $key = '')`

Extracts field with provided key from RPC response `data` object or return `data` as an array if the key is empty.

#### responseAsArray

`public function responseAsArray(): array`

Returns response as an array using json_decode.

#### seeJsonRpc

`public function seeJsonRpc(array $data = []): self`

Asserts that the response contains provided data.

#### seeJsonRpcError

`public function seeJsonRpcError(array $data = []): self`

Asserts that the error response contains provided data.

### Package testing

You can run the tests with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details and a todo list.

## Security

If you discover any security-related issues, please email [pkgsecurity@boomdraw.com](mailto:pkgsecurity@boomdraw.com) instead of using the issue tracker.

## License

[MIT](LICENSE)
