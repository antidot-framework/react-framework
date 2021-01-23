<?php

declare(strict_types=1);

namespace Antidot\React;

use Antidot\Application\Http\Application;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Http\Middleware\LimitConcurrentRequestsMiddleware;
use React\Http\Middleware\RequestBodyBufferMiddleware;
use React\Http\Middleware\RequestBodyParserMiddleware;
use React\Http\Middleware\StreamingRequestMiddleware;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class ServerFactory
{
    public function __invoke(ContainerInterface $container): Server
    {
        $application = $container->get(Application::class);
        assert($application instanceof ReactApplication);
        /** @var LoopInterface $loop */
        $loop = $container->get(LoopInterface::class);
        /** @var array<string, array> $globalConfig */
        $globalConfig = $container->get('config');
        /** @var array<string, int|null> $config */
        $config = $globalConfig['server'];

        $server = new Server(
            $loop,
            new StreamingRequestMiddleware(),
            new LimitConcurrentRequestsMiddleware(($config['max_concurrency']) ?? 100),
            new RequestBodyBufferMiddleware($config['buffer_size'] ?? 4 * 1024 * 1024), // 4 MiB
            new RequestBodyParserMiddleware(),
            static fn (ServerRequestInterface $request): PromiseInterface => resolve($application->handle($request))
        );

        return $server;
    }
}
