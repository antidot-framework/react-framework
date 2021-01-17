<?php

declare(strict_types=1);

namespace AntidotTest\React;

use Antidot\React\LoopFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use React\EventLoop\StreamSelectLoop;

class LoopFactoryTest extends TestCase
{
    public function testItShouldCreateInstancesOfLoopInterface(): void
    {
        $factory = new LoopFactory();
        $loop = $factory($this->createMock(ContainerInterface::class));
        $this->assertInstanceOf(StreamSelectLoop::class, $loop);
    }
}
