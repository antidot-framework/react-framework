<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Response\ErrorResponseGenerator;
use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Container\MiddlewareFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use React\Promise\PromiseInterface;
use RuntimeException;
use Throwable;
use function React\Promise\resolve;

class ReactApplication implements Application, RequestHandlerInterface, MiddlewareInterface
{
    private MiddlewarePipeline $pipeline;
    private Router $router;
    private MiddlewareFactory $middlewareFactory;
    private RouteFactory $routeFactory;
    private ErrorResponseGenerator $errorResponseGenerator;

    public function __construct(
        MiddlewarePipeline $pipeline,
        Router $router,
        MiddlewareFactory $middlewareFactory,
        RouteFactory $routeFactory,
        ErrorResponseGenerator $errorResponseGenerator
    ) {
        $this->routeFactory = $routeFactory;
        $this->middlewareFactory = $middlewareFactory;
        $this->router = $router;
        $this->pipeline = $pipeline;
        $this->errorResponseGenerator = $errorResponseGenerator;
    }

    public function pipe(string $middlewareName): void
    {
        $this->pipeline->pipe($this->middlewareFactory->create($middlewareName));
    }

    public function get(string $uri, array $middleware, string $name): void
    {
        $this->route('GET', $uri, $middleware, $name);
    }

    public function post(string $uri, array $middleware, string $name): void
    {
        $this->route('POST', $uri, $middleware, $name);
    }

    public function patch(string $uri, array $middleware, string $name): void
    {
        $this->route('PATCH', $uri, $middleware, $name);
    }

    public function put(string $uri, array $middleware, string $name): void
    {
        $this->route('PUT', $uri, $middleware, $name);
    }

    public function delete(string $uri, array $middleware, string $name): void
    {
        $this->route('DELETE', $uri, $middleware, $name);
    }

    public function options(string $uri, array $middleware, string $name): void
    {
        $this->route('OPTIONS', $uri, $middleware, $name);
    }

    public function route(string $method, string $uri, array $middleware, string $name): void
    {
        $this->router->append(
            $this->routeFactory->create([$method], $middleware, $uri, $name)
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new PromiseResponse(resolve($request)
            ->then(
                function (ServerRequestInterface $request) use ($handler): PromiseInterface {
                    $response = new PromiseResponse(
                        resolve($request)
                            ->then(static function (ServerRequestInterface $request): ServerRequestInterface {
                                return $request->withAttribute('request_id', Uuid::uuid4()->toString());
                            })
                            ->then(function (ServerRequestInterface $request) use ($handler): ResponseInterface {
                                try {
                                    return $this->pipeline->process($request, $handler);
                                } catch (Throwable $exception) {
                                    return $this->errorResponseGenerator->__invoke($exception);
                                }
                            })
                    );

                    return resolve($response);
                }
            ));
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new PromiseResponse(resolve($request)
            ->then(
                function (ServerRequestInterface $request): PromiseInterface {
                    $response = new PromiseResponse(
                        resolve($request)
                            ->then(static function (ServerRequestInterface $request): ServerRequestInterface {
                                return $request->withAttribute('request_id', Uuid::uuid4()->toString());
                            })
                            ->then(function (ServerRequestInterface $request): ResponseInterface {
                                try {
                                    return $this->pipeline->handle($request);
                                } catch (Throwable $exception) {
                                    return $this->errorResponseGenerator->__invoke($exception);
                                }
                            })
                    );

                    return resolve($response);
                }
            ));
    }

    public function run(): void
    {
        throw new RuntimeException('You can\'t run application out of React PHP server.');
    }
}
