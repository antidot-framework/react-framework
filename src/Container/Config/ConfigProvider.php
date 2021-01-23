<?php

namespace Antidot\React\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\React\LoopFactory;
use Antidot\React\ReactApplicationFactory;
use Antidot\React\ServerFactory;
use Antidot\React\SocketFactory;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Socket\Server as Socket;

class ConfigProvider
{
    private const DEFAULT_HOST = '0.0.0.0';
    private const DEFAULT_PORT = 8080;
    private const DEFAULT_CONCURRENCY = 100;
    private const DEFAULT_BUFFER_SIZE = 4 * 1024 * 1024;

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'factories' => [
                    Application::class => ReactApplicationFactory::class,
                    LoopInterface::class => LoopFactory::class,
                    Server::class => ServerFactory::class,
                    Socket::class => SocketFactory::class,
                ],
            ],
            'server' => [
                'workers' => 1,
                'host' => self::DEFAULT_HOST,
                'port' => self::DEFAULT_PORT,
                'max_concurrency' => self::DEFAULT_CONCURRENCY,
                'buffer_size' => self::DEFAULT_BUFFER_SIZE,
            ]
        ];
    }
}
