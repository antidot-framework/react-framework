<?php

declare(strict_types=1);

namespace AntidotTest\React\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\React\Container\Config\ConfigProvider;
use Antidot\React\LoopFactory;
use Antidot\React\ReactApplicationFactory;
use Antidot\React\RunServerCommandFactory;
use Antidot\React\ServerFactory;
use Antidot\React\SocketFactory;
use Antidot\React\WatchServerCommandFactory;
use Drift\Server\Console\RunServerCommand;
use Drift\Server\Console\WatchServerCommand;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Socket\Server as Socket;

class ConfigProviderTest extends TestCase
{
    public function testItShouldReturnTheConfigArray(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertIsArray($configProvider());
        $this->assertSame(
            [
                'dependencies' => [
                    'factories' => [
                        Application::class => ReactApplicationFactory::class,
                        LoopInterface::class => LoopFactory::class,
                        Server::class => ServerFactory::class,
                        Socket::class => SocketFactory::class,
                    ]
                ],
                'console' => [
                    'commands' => [
                        'server:run' => RunServerCommand::class,
                        'server:watch' => WatchServerCommand::class
                    ],
                    'factories' => [
                        RunServerCommand::class => RunServerCommandFactory::class,
                        WatchServerCommand::class => WatchServerCommandFactory::class,
                    ],
                ],
                'server' => [
                    'host' => '0.0.0.0',
                    'port' => 5555,
                    'buffer_size' => 4096,
                    'max_concurrency' => 100,
                    'workers' => 1,
                    'static_folder' => 'public'
                ]
            ],
            $configProvider(),
        );
    }
}
