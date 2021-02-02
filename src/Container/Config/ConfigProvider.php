<?php

namespace Antidot\React\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\React\LoopFactory;
use Antidot\React\ReactApplicationFactory;
use Antidot\React\RunServerCommandFactory;
use Antidot\React\ServerFactory;
use Antidot\React\SocketFactory;
use Antidot\React\WatchServerCommandFactory;
use Drift\Server\Console\RunServerCommand;
use Drift\Server\Console\WatchServerCommand;
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
            'console' => $this->getConsoleConfig(),
            'server' => [
                'host' => '0.0.0.0',
                'port' => 5555,
                'buffer_size' => 4096,
                'max_concurrency' => 100,
                'workers' => 1,
                'static_folder' => 'public'
            ]
        ];
    }

    private function getConsoleConfig(): array
    {
        $hasWatcher = class_exists(WatchServerCommand::class);

        return $hasWatcher
            ? [
                'commands' => [
                    'server:run' => RunServerCommand::class,
                    'server:watch' => WatchServerCommand::class
                ],
                'factories' => [
                    RunServerCommand::class => RunServerCommandFactory::class,
                    WatchServerCommand::class => WatchServerCommandFactory::class,
                ]
            ]
            : [
                'commands' => [
                    'server:run' => RunServerCommand::class,
                ],
                'factories' => [
                    RunServerCommand::class => RunServerCommandFactory::class,
                ]
            ];
    }
}
