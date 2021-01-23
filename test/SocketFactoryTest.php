<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Antidot\React\SocketFactory;
use Assert\AssertionFailedException;
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
                ['server' => ['workers' => 1, 'host' => '0.0.0.0', 'port' => 8080]]
            );

        $factory = new SocketFactory();
        $socket = $factory($container);
        $this->assertInstanceOf(Socket::class, $socket);
    }

    /** @dataProvider getInvalidConfig */
    public function testItShouldThrowExceptionWithInvalidConfig(array $config): void
    {
        $this->expectException(AssertionFailedException::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([LoopInterface::class], ['config'])
            ->willReturnOnConsecutiveCalls(
                $this->createMock(LoopInterface::class),
                $config
            );

        $factory = new SocketFactory();
        $factory($container);
    }

    public function getInvalidConfig()
    {
        return [
            'Bad Host' => [
                ['server' => ['port' => 8080, 'workers' => 3, 'host' => '756875.67867.7668.787']]
            ],
            [
                ['server' => ['host' => '0.0.0.0', 'port' => ['test'], 'workers' => 3]]
            ],
            [
                ['server' => ['host' => '0.0.0.0', 'port' => 8888, 'workers' => 'some']]
            ],
            [
                ['server' => ['port' => 43525, 'workers' => 3]]
            ],
            [
                ['server' => ['port' => 43525, 'host' => '0.0.0.0']]
            ],
            [
                ['server' => ['host' => '0.0.0.0', 'workers' => 3]]
            ],
        ];
    }
}
