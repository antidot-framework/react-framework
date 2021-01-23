<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Antidot\Application\Http\Application;
use Antidot\Application\Http\Response\ErrorResponseGenerator;
use Antidot\React\ReactApplication;
use Antidot\React\ServerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server;

class ServerFactoryTest extends TestCase
{
    public function testItShouldCreateReactServerInstances(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive([Application::class], [LoopInterface::class], ['config'])
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ReactApplication::class),
                $this->createMock(LoopInterface::class),
                ['server' => ['max_concurrency' => 100, 'buffer_size' => 43242]]
            );
        $factory = new ServerFactory();
        $server = $factory($container);
        $this->assertInstanceOf(Server::class, $server);
    }

    public function testItShouldThrowExceptionWithNonReactApplicationInstance(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(Application::class)
            ->willReturn(
                $this->createMock(Application::class)
            );
        $factory = new ServerFactory();
        $factory($container);
    }

    /** @dataProvider getInvalidConfig */
    public function testItShouldThrowExceptionWithInvalidConfig(array $config): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive([Application::class], [LoopInterface::class], ['config'])
            ->willReturnOnConsecutiveCalls(
                $this->createMock(ReactApplication::class),
                $this->createMock(LoopInterface::class),
                $config
            );
        $factory = new ServerFactory();
        $factory($container);
    }

    public function getInvalidConfig()
    {
        return [
            [
                ['server' => ['max_concurrency' => 100]]
            ],
            [
                ['server' => ['buffer_size' => 43525]]
            ],
            [
                ['server' => ['max_concurrency' => 'hello', 'buffer_size' => 43525]]
            ],
            [
                ['server' => ['max_concurrency' => 100, 'buffer_size' => []]]
            ],
        ];
    }
}
