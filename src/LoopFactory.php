<?php

declare(strict_types=1);

namespace Antidot\React;

use Psr\Container\ContainerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class LoopFactory
{
    public function __invoke(ContainerInterface $container): LoopInterface
    {
        return Factory::create();
    }
}
