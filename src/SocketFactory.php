<?php

declare(strict_types=1);

namespace Antidot\React;

use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Socket;

class SocketFactory
{
    public function __invoke(ContainerInterface $container): Socket
    {
        /** @var LoopInterface $loop */
        $loop = $container->get(LoopInterface::class);
        /** @var array<string, array> $globalConfig */
        $globalConfig = $container->get('config');
        /** @var array<string, string|null> $config */
        $config = $globalConfig['server'];

        return new Socket(
            sprintf('%s:%s', $config['host'] ?? '0.0.0.0', $config['port'] ?? '8080'),
            $loop,
            ['tcp' => ['so_reuseport' => 2 <= $config['workers']]]
        );
    }
}
