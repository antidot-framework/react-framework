<?php

declare(strict_types=1);

namespace Antidot\React;

use Drift\Server\Console\WatchServerCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

class WatchServerCommandFactory
{
    public function __invoke(ContainerInterface $container): WatchServerCommand
    {
        /** @var array<string, array> $globalConfig */
        $globalConfig = $container->get('config');
        /** @var array<string, string|int> $config */
        $config = $globalConfig['server'];

        $command = new WatchServerCommand(dirname('./'), [
            'bin/console',
            'server:run',
            sprintf('--adapter=%s', DriftKernelAdapter::class),
            '--debug',
        ], 'server:watch');
        $definition = $command->getDefinition();
        $command->setDescription('Watch Drift HTTP Server for development purposes');

        $path = new InputArgument('path', InputArgument::OPTIONAL, $definition->getArgument('path')->getDescription());
        $path->setDefault(sprintf('%s:%s', $config['host'], $config['port']));
        $definition->setArguments([$path]);

        $staticFolder = $definition->getOption('static-folder');
        $staticFolder->setDefault((string)$config['static_folder']);
        $concurrentRequests = $definition->getOption('concurrent-requests');
        $concurrentRequests->setDefault((string)$config['max_concurrency']);
        $bufferSize = $definition->getOption('request-body-buffer');
        $bufferSize->setDefault((string)$config['buffer_size']);

        return $command;
    }
}
