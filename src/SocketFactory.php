<?php

declare(strict_types=1);

namespace Antidot\React;

use Assert\Assertion;
use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Server as Socket;

class SocketFactory
{
    private const DEFAULT_TCP_CONFIG = ['tcp' => ['so_reuseport' => false]];
    private const REUSE_PORT = true;
    private const DEFAULT_WORKERS_NUMBER = 1;

    public function __invoke(ContainerInterface $container): Socket
    {
        /** @var LoopInterface $loop */
        $loop = $container->get(LoopInterface::class);
        /** @var array<string, array> $globalConfig */
        $globalConfig = $container->get('config');
        /** @var array<string, string|null> $config */
        $config = $globalConfig['server'];
        Assertion::notEmptyKey($config, 'host');
        Assertion::notEmptyKey($config, 'port');
        Assertion::keyExists($config, 'workers');
        /** @var string $host */
        $host = $config['host'];
        Assertion::ipv4($host);
        /** @var int $port */
        $port = $config['port'];
        Assertion::integer($port);
        /** @var int $workersNumber */
        $workersNumber = $config['workers'];
        Assertion::integer($workersNumber);
        $tcpConfig = self::DEFAULT_TCP_CONFIG;
        if ($this->needMoreThanOne($workersNumber)) {
            $tcpConfig['tcp']['so_reuseport'] = self::REUSE_PORT;
        }

        return new Socket(sprintf('%s:%s', $host, $port), $loop, $tcpConfig);
    }

    private function needMoreThanOne(int $workersNumber): bool
    {
        return self::DEFAULT_WORKERS_NUMBER < $workersNumber;
    }
}
