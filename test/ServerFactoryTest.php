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
                ['server' => [

                ]]
            );
        $factory = new ServerFactory();
        $server = $factory($container);
        $this->assertInstanceOf(Server::class, $server);
    }
}
