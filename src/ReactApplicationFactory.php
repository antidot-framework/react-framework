<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\Response\ErrorResponseGenerator;
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
        /** @var ErrorResponseGenerator $errorResponseGenerator */
        $errorResponseGenerator = $container->get(ErrorResponseGenerator::class);

        return new ReactApplication(
            new MiddlewarePipeline(),
            $router,
            $middlewareFactory,
            $routeFactory,
            $errorResponseGenerator
        );
    }
}
