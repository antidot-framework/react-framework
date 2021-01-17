<?php

namespace AntidotTest\React\Container\Config;

use Antidot\Application\Http\Application;
use Antidot\React\Container\Config\ConfigProvider;
use Antidot\React\LoopFactory;
use Antidot\React\ReactApplicationFactory;
use Antidot\React\ServerFactory;
use Antidot\React\SocketFactory;
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
                'server' => []
            ],
            $configProvider(),
        );
    }
}
