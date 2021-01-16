<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\Application;
use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Container\MiddlewareFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use function React\Promise\resolve;

class ReactApplication implements Application, RequestHandlerInterface, MiddlewareInterface
{
    private MiddlewarePipeline $pipeline;
    private Router $router;
    private MiddlewareFactory $middlewareFactory;
    private RouteFactory $routeFactory;

    public function __construct(
        MiddlewarePipeline $pipeline,
        Router $router,
        MiddlewareFactory $middlewareFactory,
        RouteFactory $routeFactory
    ) {
        $this->routeFactory = $routeFactory;
        $this->middlewareFactory = $middlewareFactory;
        $this->router = $router;
        $this->pipeline = $pipeline;
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
                fn(ServerRequestInterface $request): ResponseInterface  => $this->pipeline->process($request, $handler)
            ));
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new PromiseResponse(resolve($request)
            ->then(
                fn (ServerRequestInterface $request): ResponseInterface => $this->pipeline->handle($request)
            ));
    }

    public function run(): void
    {
        throw new RuntimeException('You can\'t run application out of React PHP server.');
    }
}
