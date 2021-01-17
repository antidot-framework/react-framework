<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Antidot\React\SocketFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Socket;

class SocketFactoryTest extends TestCase
{
    public function testItShouldcreateReactSocketInstances(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([LoopInterface::class], ['config'])
            ->willReturnOnConsecutiveCalls(
                $this->createMock(LoopInterface::class),
                ['server' => []]
            );

        $factory = new SocketFactory();
        $socket = $factory($container);
        $this->assertInstanceOf(Socket::class, $socket);
    }
}
