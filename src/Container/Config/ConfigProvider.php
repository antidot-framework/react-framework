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
                'workers' => 1
            ]
        ];
    }
}
