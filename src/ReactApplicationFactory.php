<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Container\MiddlewareFactory;
use Psr\Container\ContainerInterface;

class ReactApplicationFactory
{
    public function __invoke(ContainerInterface $container): ReactApplication
    {
        /** @var Router $router */
        $router = $container->get(Router::class);
        /** @var MiddlewareFactory $middlewareFactory */
        $middlewareFactory = $container->get(MiddlewareFactory::class);
        /** @var RouteFactory $routeFactory */
        $routeFactory = $container->get(RouteFactory::class);

        return new ReactApplication(
            new MiddlewarePipeline(),
            $router,
            $middlewareFactory,
            $routeFactory
        );
    }
}
